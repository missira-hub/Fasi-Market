<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Event;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{public function createCheckoutSession(Request $request)
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
        return response()->json(['error' => 'Order not found.'], 404);
    }

    // Get first item's product → farmer
    $firstItem = $order->items->first();
    if (!$firstItem || !$firstItem->product || !$firstItem->product->user) {
        return response()->json(['error' => 'Invalid order items.'], 400);
    }

    $farmer = $firstItem->product->user;

    // 🔑 CRITICAL: Farmer must have a Stripe account
    if (!$farmer->stripe_account_id) {
        return response()->json(['error' => 'Farmer not onboarded for payments.'], 400);
    }

    // Calculate total in **cents/kurus** (Stripe uses smallest currency unit)
    $totalAmount = $order->items->sum(fn($item) => $item->price * $item->quantity); // in TRY
    $totalInKurus = (int) round($totalAmount * 100); // e.g., ₺100 → 10000 kurus

    if ($totalInKurus <= 0) {
        return response()->json(['error' => 'Invalid order amount.'], 400);
    }

    // 🔑 Platform takes 10% fee
    $platformFeeKurus = (int) round($totalInKurus * 0.10); // 10%

    // Farmer gets the rest
    $farmerAmountKurus = $totalInKurus - $platformFeeKurus;

    try {
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'try',
                    'product_data' => ['name' => 'Order #' . $order->id],
                    'unit_amount' => $totalInKurus, // total charged to customer
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => config('app.frontend_url') . '/payment-success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.frontend_url') . '/payment-cancel',
            'metadata' => ['order_id' => $order->id],
            'payment_intent_data' => [
                'application_fee_amount' => $platformFeeKurus, // goes to platform (you)
                'transfer_data' => [
                    'destination' => $farmer->stripe_account_id, // farmer gets $farmerAmountKurus
                    'amount' => $farmerAmountKurus,
                ],
            ],
        ]);

        return response()->json(['sessionId' => $session->id]);
    } catch (\Exception $e) {
        \Log::error('Stripe session failed', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Payment setup failed.'], 500);
    }
}

}
