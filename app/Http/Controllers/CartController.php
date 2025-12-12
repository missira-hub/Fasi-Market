<?php


namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    private function authorizeConsumer()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'consumer') {
            abort(403, 'Access denied. Only consumers can access the cart.');
        }
    }

    // List all cart items for the logged-in consumer
  public function index(Request $request)
{
    $this->authorizeConsumer();

    $user = $request->user();

    $cartItems = Cart::with('product')
        ->where('user_id', $user->id)
        ->get();

    $formattedCart = [];
    $totalCartValue = 0;

    foreach ($cartItems as $item) {
        if (!$item->product) {
            continue; // Skip if product doesn't exist
        }

        $lineTotal = $item->product->price * $item->quantity;
        $totalCartValue += $lineTotal;

        $formattedCart[] = [
            'id' => $item->id,
            'product_id' => $item->product->id,
            'name' => $item->product->name,
            'description' => $item->product->description,   // Add description
            'quantity' => $item->quantity,
            'unit_price' => $item->product->price,
            'total_price' => $lineTotal,
            'image' => $item->product->image,               // Add image path here
        ];
    }

    return response()->json([
        'message' => 'Cart items retrieved successfully',
        'cart' => $formattedCart,
        'total_cart_value' => $totalCartValue
    ]);
}

    // Add product to cart for logged-in consumer
    public function store(Request $request)
{
    $this->authorizeConsumer();

    $request->validate([
        'product_id' => 'required|integer',
        'quantity' => 'required|integer|min:1',
    ]);

    $user = $request->user();

    // Check if product exists and is not soft-deleted
    $product = \App\Models\Product::where('id', $request->product_id)->first();

    if (!$product) {
        return response()->json(['message' => 'Product not found or unavailable.'], 404);
    }

    // Optional: Check if requested quantity is available in stock
    if ($request->quantity > $product->quantity) {
        return response()->json(['message' => 'Not enough quantity available.'], 400);
    }

    // Check if the product is already in the cart
    $cartItem = Cart::where('user_id', $user->id)
        ->where('product_id', $request->product_id)
        ->first();

    if ($cartItem) {
        $cartItem->quantity += $request->quantity;
        $cartItem->save();
    } else {
        $cartItem = Cart::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ]);
    }

    return response()->json([
        'message' => 'Product added to cart successfully',
        'cart_item' => $cartItem
    ], 201);
}

    // Update quantity of a cart item
    public function update(Request $request, $id)
{
    $request->validate([
        'quantity' => 'required|integer|min:0',
    ]);

    $user = $request->user();

    $cartItem = Cart::where('id', $id)
        ->where('user_id', $user->id)
        ->firstOrFail();

    if ($request->quantity == 0) {
        // Delete if quantity is zero
        $cartItem->delete();

        return response()->json(['message' => 'Cart item removed because quantity was set to zero.']);
    }

    $cartItem->quantity = $request->quantity;
    $cartItem->save();

    return response()->json($cartItem);
}


    // Remove a cart item
    public function destroy(Request $request, $id)
    {
        $this->authorizeConsumer();

        $user = $request->user();

        $cartItem = Cart::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $cartItem->delete();

        return response()->json(['message' => 'Cart item deleted successfully']);
    }
    public function clear(Request $request)
{
    $this->authorizeConsumer();

    $user = $request->user();

    // Delete all cart items for this user
    Cart::where('user_id', $user->id)->delete();

    return response()->json([
        'message' => 'Cart cleared successfully'
    ]);
}
public function getTotal(Request $request)
{
    $total = $request->user()->cartItems()
        ->with('product')
        ->get()
        ->sum(fn($item) => $item->product ? $item->product->price * $item->quantity : 0);

    return response()->json(['total' => $total]);
}


}
