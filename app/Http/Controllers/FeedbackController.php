<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    // 1. Submit feedback for a product (once per product per user)
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'required|string',
        ]);

        $userId = Auth::id();

        // Check if user already left feedback for this product
        $exists = Feedback::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You already left feedback for this product.'], 422);
        }

        $feedback = Feedback::create([
            'user_id'    => $userId,
            'product_id' => $request->product_id,
            'rating'     => $request->rating,
            'comment'    => $request->comment,
            'approved'   => false, // set false initially for moderation
        ]);

        return response()->json([
            'message' => 'Feedback submitted. Awaiting approval.',
            'data'    => $feedback,
        ]);
    }

    // 2. Update feedback (only if owner)
  public function update(Request $request, Feedback $feedback)
{
    if ($feedback->user_id !== Auth::id()) {
        return response()->json(['message' => 'Unauthorized.'], 403);
    }

    // 🚨 Block update if farmer already replied
    if ($feedback->reply) {
        return response()->json([
            'message' => 'You cannot edit this review because the farmer has already replied.'
        ], 403);
    }

    $request->validate([
        'rating'  => 'required|integer|min:1|max:5',
        'comment' => 'required|string',
    ]);

    $feedback->update($request->only(['rating', 'comment']));

    return response()->json([
        'message' => 'Feedback updated.',
        'data'    => $feedback
    ]);
}


// app/Http/Controllers/FeedbackController.php
public function destroy(Request $request, $id)
{
    $feedback = Feedback::findOrFail($id);
    $user = $request->user();

    // Farmers can only delete feedback on their own products
    if ($user->role === 'farmer') {
        if ($feedback->product->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    } 
    // Users can only delete their own feedback
    else if ($feedback->user_id !== $user->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $feedback->delete();
    return response()->json(['message' => 'Feedback deleted successfully']);
}



    // 4. View approved feedback for a product
    public function productFeedback($productId)
    {
        $feedback = Feedback::where('product_id', $productId)
            ->where('approved', true)
            ->with('user:id,name') // load reviewer name
            ->latest()
            ->paginate(10);

        return response()->json([
            'product_id' => $productId,
            'feedback'   => $feedback->items(),
            'pagination' => [
                'total'        => $feedback->total(),
                'per_page'     => $feedback->perPage(),
                'current_page' => $feedback->currentPage(),
                'last_page'    => $feedback->lastPage(),
            ],
        ]);
    }

    // 5. View feedback for the farmer’s products
    public function farmerFeedback()
    {
        $farmer = Auth::user();

        $feedback = Feedback::whereHas('product', function ($q) use ($farmer) {
            $q->where('user_id', $farmer->id);
        })
        ->with('user:id,name', 'product:id,name')
        ->latest()
        ->paginate(10);

        return response()->json($feedback);
    }

    // 6. Farmer approves feedback
    public function approve(Request $request, $id)
    {
        $feedback = Feedback::with('product')->findOrFail($id);

        if (!$feedback->product || $feedback->product->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $feedback->approved = true;
        $feedback->save();

        return response()->json(['message' => 'Feedback approved.']);
    }

    // 7. Farmer replies to feedback
  public function reply(Request $request, $id)
{
    $request->validate([
        'reply' => 'required|string|max:1000',
    ]);


   $feedback = Feedback::find($id);
if (!$feedback) {
    return response()->json(['message' => 'Feedback not found'], 404);
}

$product = Product::find($feedback->product_id);
if (!$product) {
    return response()->json(['message' => 'Product not found'], 404);
}

if ($product->user_id !== $request->user()->id) {
    return response()->json(['message' => 'Unauthorized'], 403);
}


    $feedback->reply = $request->reply;
    $feedback->save();

    return response()->json(['message' => 'Reply saved', 'reply' => $feedback->reply]);
}


    // 8. View all approved feedback across the system
    public function allApproved()
    {
        $feedback = Feedback::where('approved', true)
            ->with('user:id,name', 'product:id,name')
            ->latest()
            ->paginate(15);

        return response()->json([
            'feedback'   => $feedback->items(),
            'pagination' => [
                'total'        => $feedback->total(),
                'per_page'     => $feedback->perPage(),
                'current_page' => $feedback->currentPage(),
                'last_page'    => $feedback->lastPage(),
            ],
        ]);
    }

    // 9. View reviews submitted by the current authenticated user
    public function userReviews()
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'Unauthenticated'], 401);
    }

    $reviews = Feedback::where('user_id', $user->id)
        ->with(['product.user:id,name'])  // eager load farmer user on product
        ->get();

    return response()->json($reviews);
}




     public function index(Request $request)
    {
        try {
            $feedbacks = Feedback::with(['user', 'product'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json($feedbacks);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch feedback.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
