<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Models\OrderAddress; 

class OrderController extends Controller
{
    /**
     * Buy a single product (Buy Now)
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $user = $request->user();

        DB::beginTransaction();
        try {
            $product = Product::lockForUpdate()->findOrFail($request->product_id);

            if ($product->quantity < $request->quantity) {
                return response()->json(['message' => 'Insufficient stock'], 400);
            }

            $totalPrice = $product->price * $request->quantity;

            $order = Order::create([
                'user_id'     => $user->id,
                'total_price' => $totalPrice,
                'status'      => 'pending',
            ]);

            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $product->id,
                'quantity'   => $request->quantity,
                'price'      => $product->price,
            ]);

            $product->decrement('quantity', $request->quantity);

            DB::commit();

            return response()->json([
                'message'  => 'Order placed successfully.',
                'order_id' => $order->id,
                'total'    => $totalPrice,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Order failed',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Checkout all items in cart
     */
/**
 * Create a pending order from cart (for Stripe payment)
 */
/**
 * Checkout all items in cart
 */
// OrderController.php


public function checkout(Request $request)
{
    // ✅ Validate structured address + phone
    $request->validate([
        'customer_phone' => 'required|string|max:20',
        'address.street' => 'required|string|max:255',
        'address.city' => 'required|string|max:100',
        'address.postal_code' => 'nullable|string|max:20',
        'address.country' => 'required|string|max:100',
        'delivery_method' => 'required|in:delivery,pickup',
        'payment_method' => 'required|in:card,cod'
    ]);

    $user = auth()->user();

    DB::beginTransaction();
    try {
        // 1. Create the order (without full_address)
        $order = Order::create([
            'user_id' => $user->id,
            'customer_phone' => $request->customer_phone, // ✅ Save phone
            'delivery_method' => $request->delivery_method,
            'status' => $request->payment_method === 'cod' ? 'paid' : 'pending',
            'total_price' => 0,
        ]);

        // 2. Save structured address in order_addresses table
        OrderAddress::create([
            'order_id' => $order->id,
            'street' => $request->address['street'],
            'city' => $request->address['city'],
            'postal_code' => $request->address['postal_code'] ?? null,
            'country' => $request->address['country'],
            // Optional: generate full_address for legacy compatibility
            'full_address' => implode(', ', array_filter([
                $request->address['street'],
                $request->address['city'],
                $request->address['postal_code'],
                $request->address['country']
            ]))
        ]);

        // 3. Load cart items
        $cartItems = Cart::where('user_id', $user->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            DB::rollBack();
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        $total = 0;

        // 4. Create order items
        foreach ($cartItems as $cartItem) {
            if (!$cartItem->product) continue;

            $price = $cartItem->product->price;
            $quantity = $cartItem->quantity;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $quantity,
                'price' => $price,
            ]);

            $total += $price * $quantity;
        }

        // 5. Update total price
        $order->total_price = $total;
        $order->save();

        // 6. Clear cart
        Cart::where('user_id', $user->id)->delete();

        DB::commit();

        return response()->json([
            'message' => 'Order created successfully',
            'order_id' => $order->id
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Checkout failed: ' . $e->getMessage());
        return response()->json([
            'error' => 'Order creation failed',
            'message' => $e->getMessage()
        ], 500);
    }
}


   public function userOrders()
{
    $orders = Order::with('items.product')
        ->where('user_id', auth()->id())
        ->orderByDesc('created_at')
        ->paginate(10);

    $orders->getCollection()->transform(function ($order) {
        $calculatedTotal = $order->items->sum(fn($item) => $item->price * $item->quantity);

        return [
            'order_id'    => $order->id,
            'order_date'  => $order->created_at->format('F j, Y, g:i a'),
            'total_price' => (float) $calculatedTotal,
            'status'      => $order->status ?? 'Completed',
            'items_count' => $order->items->count(),
            'items'       => $order->items->map(fn($item) => [
                'product_name' => $item->product->name ?? 'Deleted Product',
                'quantity'     => $item->quantity,
                'price_each'   => (float) $item->price,
                'subtotal'     => (float) ($item->price * $item->quantity),
            ]),
        ];
    });

    return response()->json([
        'data'       => $orders->items(),
        'pagination' => [
            'current_page' => $orders->currentPage(),
            'last_page'    => $orders->lastPage(),
            'per_page'     => $orders->perPage(),
            'total'        => $orders->total(),
        ],
    ]);
}

/**
 * Farmer's actual orders containing their products
 */
public function farmerProductOrders(Request $request)
{
    $user = $request->user();
    
    if (!$user || $user->role !== 'farmer') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    try {
        $orders = Order::with([
                'items.product:id,user_id,name,price,image,unit_id',
                'items.product.unit:id,name,abbreviation',
                'user:id,name,phone'
            ])
            ->whereHas('items.product', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereIn('status', ['paid', 'shipped', 'delivered'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $fullAddress = '';
                if (isset($order->address)) {
                    $fullAddress = $order->address->full_address ?? '';
                } else {
                    $addressParts = [];
                    if (!empty($order->street)) $addressParts[] = $order->street;
                    if (!empty($order->city)) $addressParts[] = $order->city;
                    if (!empty($order->postal_code)) $addressParts[] = $order->postal_code;
                    if (!empty($order->country)) $addressParts[] = $order->country;
                    $fullAddress = implode(', ', $addressParts);
                }

                return [
                    'id' => $order->id,
                    'total_price' => $order->total_price ?? 0,
                    'status' => $order->status ?? 'paid',
                    'delivery_method' => $order->delivery_method ?? 'delivery',
                    'created_at' => $order->created_at,
                    'shipped_at' => $order->shipped_at,
                    'customer_name' => $order->user?->name ?? 'Unknown Customer',
                    'customer_phone' => $order->customer_phone ?? $order->user?->phone ?? '',
                    'shipping_address' => $fullAddress ?: 'No address provided',
                    'items' => $order->items->map(function ($item) {
                        // ✅ NULL-SAFE PRODUCT HANDLING
                        if (!$item->product) {
                            return [
                                'id' => $item->id,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'product' => [
                                    'id' => null,
                                    'name' => 'Deleted Product',
                                    'image' => null,
                                    'unit' => null
                                ]
                            ];
                        }

                        return [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'product' => [
                                'id' => $item->product->id,
                                'name' => $item->product->name,
                                'image' => $item->product->image,
                                'unit' => $item->product->unit ? [
                                    'id' => $item->product->unit->id,
                                    'abbreviation' => $item->product->unit->abbreviation
                                ] : null
                            ]
                        ];
                    })
                ];
            });

        return response()->json($orders);
    } catch (\Exception $e) {
        \Log::error('farmerProductOrders failed: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'error' => 'Failed to load orders',
            'message' => $e->getMessage()
        ], 500);
    }
}


public function salesHistory(Request $request)
{
    $user = $request->user();
    
    if (!$user || $user->role !== 'farmer') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    try {
        // Get ALL order items where product belongs to this farmer AND order is paid
        $sales = OrderItem::with([
                'product:id,name,image,unit_id,user_id', // include user_id for "You" check
                'product.unit:id,name,abbreviation',
                'order:id,user_id,total_price,created_at,status'
            ])
            ->whereHas('product', function ($query) use ($user) {
                $query->where('user_id', $user->id); // Farmer's products
            })
            ->whereHas('order', function ($query) {
                $query->where('status', 'paid'); // Only successful sales
            })
            ->orderBy('created_at', 'desc') // Newest first
            ->get()
            ->map(function ($item) use ($user) {
                return [
                    'id' => $item->id,
                    'order_id' => $item->order->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'image' => $item->product->image,
                        'unit' => $item->product->unit ? [
                            'id' => $item->product->unit->id,
                            'abbreviation' => $item->product->unit->abbreviation
                        ] : null,
                        // ✅ Add user context for "You" vs actual farmer name
                        'user' => [
                            'id' => $item->product->user_id,
                            'name' => $item->product->user_id === $user->id ? 'You' : $user->name
                        ]
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->price,
                    'total_price' => (float) ($item->quantity * $item->price),
                    'created_at' => $item->created_at
                ];
            });

        return response()->json($sales);
    } catch (\Exception $e) {
        \Log::error('Sales history error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to load sales'], 500);
    }
}

public function destroy($id)
{
    $order = Order::with('items')->find($id);

    if (!$order) {
        return response()->json(['message' => 'Order not found'], 404);
    }

    if ($order->user_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    try {
        // Delete related order items first (if foreign key constraints exist)
        if ($order->items()->exists()) {
            $order->items()->delete();
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to delete order',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function updateStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        $order = Order::findOrFail($orderId);
        $order->status = $request->status;

        // If marking delivered, also set shipped_at timestamp
        if ($request->status === 'delivered') {
            $order->shipped_at = now();
        }

        $order->save();

        return response()->json([
            'message' => 'Order status updated successfully',
            'order'   => $order
        ]);
    }
}
