<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
   use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Order;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;
use App\Models\User; 

use App\Models\WebhookLog; // Add this at top





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

    $order = Order::with(['items.product.user'])
        ->where('id', $request->order_id)
        ->where('user_id', $buyer->id)
        ->first();

    if (!$order) {
        return response()->json(['error' => 'Order not found or does not belong to this user.'], 404);
    }

    $farmer = $order->items->first()->product->user;

    if (!$farmer->stripe_account_id) {
        return response()->json(['error' => 'Farmer is not set up for payments.'], 400);
    }

    $totalAmount = $order->items->sum(fn($item) => $item->product->price * $item->quantity);

    if ($totalAmount <= 0) {
        return response()->json(['error' => 'Invalid order amount.'], 400);
    }

    // Platform fee (10%)
    $platformFee = (int) round($totalAmount * 0.10 * 100);
    $totalInKurus = (int) round($totalAmount * 100);

    try {
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'try',
                    'product_data' => [
                        'name' => 'Order #' . $order->id,
                    ],
                    'unit_amount' => $totalInKurus,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => config('app.frontend_url') . '/payment-success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.frontend_url') . '/payment-cancelled',
            'metadata' => ['order_id' => $order->id],
            'payment_intent_data' => [
                'application_fee_amount' => $platformFee,
                'transfer_data' => [
                    'destination' => $farmer->stripe_account_id,
                ],
            ],
        ]);

        return response()->json(['sessionId' => $session->id]);

    } catch (\Exception $e) {
        \Log::error('Stripe session creation failed', [
            'order_id' => $order->id,
            'message' => $e->getMessage(),
        ]);

        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function handleWebhook(Request $request)
{
    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

    $payload = $request->getContent();
    $sig_header = $request->header('Stripe-Signature');
    $endpoint_secret = config('services.stripe.webhook_secret');

    try {
        $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        
        if ($event->type === 'checkout.session.completed') {
            $orderId = $event->data->object->metadata->order_id;
            $order = \App\Models\Order::find($orderId);
            if ($order) {
                $order->status = 'paid';
                $order->save();
                \Log::info("✅ Order #{$orderId} marked as PAID");
            }
        }

        return response('OK', 200);

    } catch (\Exception $e) {
        \Log::error('Webhook error: ' . $e->getMessage());
        return response('Webhook error', 400);
    }
}
}