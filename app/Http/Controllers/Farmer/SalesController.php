<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class SalesController extends Controller
{
    public function salesHistory(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'farmer') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Fetch orders that contain the farmer's products and are paid
            $orders = Order::with([
                    'items.product:id,name,image,unit_id,user_id',
                    'items.product.unit:id,name,abbreviation',
                    'user:id,name,phone' // customer info
                ])
                ->whereHas('items.product', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->where('status', 'paid')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($order) use ($user) {
                    // Compute total for this order (only farmer's items)
                    $farmerItemsTotal = 0;
                    $farmerItems = $order->items->filter(function ($item) use ($user) {
                        return $item->product && $item->product->user_id === $user->id;
                    })->map(function ($item) use (&$farmerItemsTotal) {
                        $subtotal = $item->quantity * $item->price;
                        $farmerItemsTotal += $subtotal;

                        return [
                            'id' => $item->id,
                            'product_name' => $item->product->name ?? 'Deleted Product',
                            'image' => $item->product->image ?? null,
                            'unit' => $item->product->unit?->abbreviation ?? null,
                            'quantity' => $item->quantity,
                            'unit_price' => (float) $item->price,
                            'total_price' => (float) $subtotal,
                        ];
                    });

                    return [
                        'order_id' => $order->id,
                        'order_date' => $order->created_at,
                        'customer' => [
                            'name' => $order->user?->name ?? 'Anonymous',
                            'phone' => $order->customer_phone ?? $order->user?->phone ?? null,
                        ],
                        'items' => $farmerItems,
                        'order_total' => (float) $order->total_price, // full order total
                        'farmer_total' => (float) $farmerItemsTotal, // only farmer's share
                    ];
                });

            return response()->json($orders);
        } catch (\Exception $e) {
            \Log::error('Sales history grouped error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load sales'], 500);
        }
    }
}