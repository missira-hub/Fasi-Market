<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Order;
use App\Models\PlatformRevenue;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function onboardFarmer($id)
    {
        $user = User::findOrFail($id);
        Stripe::setApiKey(config('services.stripe.secret'));

        $account = Account::create([
            'type' => 'express',
            'email' => $user->email,
        ]);

        $accountLink = AccountLink::create([
            'account' => $account->id,
            'refresh_url' => route('stripe.refresh'),
            'return_url' => route('stripe.success'),
            'type' => 'account_onboarding',
        ]);

        $user->stripe_account_id = $account->id;
        $user->save();

        return response()->json(['url' => $accountLink->url]);
    }

    public function createCheckoutSession(Request $request)
{
    $buyer = auth()->user();
    Stripe::setApiKey(config('services.stripe.secret'));

    $request->validate([
        'order_id' => 'required|integer|exists:orders,id'
    ]);

    // Load order with items and product
    $order = Order::with(['items.product.user'])
        ->where('id', $request->order_id)
        ->where('user_id', $buyer->id)
        ->first();

    if (!$order) {
        return response()->json(['error' => 'Order not found.'], 404);
    }

    // ✅ SAFETY: Check if order has items
    if ($order->items->isEmpty()) {
        Log::error('Order has no items', ['order_id' => $order->id]);
        return response()->json(['error' => 'Order is empty.'], 400);
    }

    $firstItem = $order->items->first();

    // ✅ SAFETY: Check if product exists
    if (!$firstItem->product) {
        Log::error('Product missing for order item', [
            'order_id' => $order->id,
            'item_id' => $firstItem->id
        ]);
        return response()->json(['error' => 'Product not found.'], 400);
    }

    $farmer = $firstItem->product->user;

    // ✅ SAFETY: Check if farmer exists and has Stripe
    if (!$farmer) {
        Log::error('Farmer not found for product', ['product_id' => $firstItem->product_id]);
        return response()->json(['error' => 'Farmer not found.'], 400);
    }

    if (!$farmer->stripe_account_id) {
        return response()->json(['error' => 'Farmer is not set up for payments.'], 400);
    }

    $totalAmount = $order->items->sum(fn($item) => $item->product->price * $item->quantity);

    if ($totalAmount <= 0) {
        return response()->json(['error' => 'Invalid order amount.'], 400);
    }

    $platformFee = (int) round($totalAmount * 0.10 * 100);
    $totalInKurus = (int) round($totalAmount * 100);

    try {
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'try',
                    'product_data' => ['name' => 'Order #' . $order->id],
                    'unit_amount' => $totalInKurus,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => config('app.frontend_url') . '/payment-success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.frontend_url') . '/payment-cancel',
            'metadata' => ['order_id' => $order->id],
            'payment_intent_data' => [
                'application_fee_amount' => $platformFee,
                'transfer_data' => ['destination' => $farmer->stripe_account_id],
            ],
        ]);

        return response()->json(['sessionId' => $session->id]);
    } catch (\Exception $e) {
        Log::error('Stripe session creation failed', [
            'order_id' => $order->id,
            'message' => $e->getMessage(),
        ]);
        return response()->json(['error' => 'Payment setup failed.'], 500);
    }
}

public function handleWebhook(Request $request)
{
    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    $payload = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');
    $webhookSecret = config('services.stripe.webhook_secret');

    try {
        // 🔒 Verify webhook signature
        $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
    } catch (\Exception $e) {
        \Log::error('Webhook signature verification failed', [
            'error' => $e->getMessage(),
            'payload' => $payload
        ]);
        return response('Invalid signature', 400);
    }

    // 🎯 Handle only the event we care about
    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;
        $orderId = $session->metadata->order_id ?? null;

        // 🔍 Validate order ID
        if (!$orderId || !is_numeric($orderId)) {
            \Log::warning('Webhook: Missing or invalid order_id', [
                'session_id' => $session->id,
                'metadata' => $session->metadata
            ]);
            return response('OK', 200); // still acknowledge
        }

        // 📦 Fetch order
        $order = Order::find((int)$orderId);
        if (!$order) {
            \Log::warning('Webhook: Order not found', ['order_id' => $orderId]);
            return response('OK', 200);
        }

        // ✅ Only process if payment succeeded AND order isn't already paid
        if ($session->payment_status === 'paid' && $order->status !== 'paid') {
            // 💰 Calculate platform fee (10%)
            $totalAmount = $session->amount_total / 100; // Convert cents/kurus to TRY
            $platformFee = round($totalAmount * 0.10, 2); // 10%

            // 🟢 Update order status
            $order->status = 'paid';
            $order->save();

            // 💵 Record platform revenue
            PlatformRevenue::create([
                'order_id' => $orderId,
                'amount' => $platformFee,
                'currency' => 'try',
                'status' => 'paid'
            ]);

            \Log::info("✅ Order #{$orderId} marked as PAID. Platform fee: ₺{$platformFee} recorded.");
        }
    }

    // 🟢 Always return 200 to acknowledge receipt
    return response('OK', 200);
}

    // ✅ Keep this — used by frontend after redirect
    public function verifyPayment($sessionId)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        try {
            $session = Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return response()->json(['error' => 'Not paid'], 400);
            }

            $orderId = $session->metadata->order_id ?? null;
            if (!$orderId) {
                return response()->json(['error' => 'No order ID'], 400);
            }

            $order = Order::find((int)$orderId);
            if ($order && $order->status !== 'paid') {
                $order->status = 'paid';
                $order->save();
                Log::info("✅ Order #{$orderId} marked as PAID via verify-payment");
            }

            return response()->json(['success' => true, 'status' => 'paid']);
        } catch (\Exception $e) {
            Log::error('verifyPayment failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Verification failed'], 500);
        }
    }

    // Optional: Keep this if you need session details elsewhere
  public function getCheckoutSession($sessionId)
{
    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    try {
        $session = \Stripe\Checkout\Session::retrieve($sessionId);
        $orderId = $session->metadata->order_id ?? null;

        \Log::info("getCheckoutSession called", [
            'session_id' => $sessionId,
            'order_id' => $orderId,
            'payment_status' => $session->payment_status
        ]);

        return response()->json([
            'payment_status' => $session->payment_status,
            'order_id' => $orderId,
        ]);
    } catch (\Exception $e) {
        \Log::error("getCheckoutSession failed", ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Session not found'], 404);
    }
}

    public function confirmPayment(Request $request)
{
    \Log::info("confirmPayment called", ['session_id' => $request->session_id]);

    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    try {
        $session = \Stripe\Checkout\Session::retrieve($request->session_id);
        $orderId = $session->metadata->order_id ?? null;

        \Log::info("Order ID from Stripe metadata", ['order_id' => $orderId]);

        if (!$orderId) {
            return response()->json(['error' => 'No order ID'], 400);
        }

        $order = \App\Models\Order::find($orderId); // This must work

        \Log::info("Order found", ['order' => $order]);

        if ($order && $order->status !== 'paid') {
            $order->status = 'paid';
            $order->save();
            \Log::info("✅ Order #$orderId marked as PAID");
        }

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        \Log::error("confirmPayment failed", ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Verification failed'], 500);
    }
}
}