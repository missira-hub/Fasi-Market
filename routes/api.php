<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ProductManagementController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\FeedbackManagementController;
use App\Models\User;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Farmer\SalesController;
use App\Http\Controllers\Farmer\FarmerOrderController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ContactController;


// ------------------- Auth Routes -------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->post('/logout-all', [AuthController::class, 'logoutAll']);

// ------------------- Profile Routes -------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', [ProfileController::class, 'show']);
    Route::put('/user/profile', [ProfileController::class, 'updateProfile']);
    Route::post('/farmer/profile', [FarmerController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
    Route::post('/user/avatar', [UserController::class, 'updateAvatar']);
});

Route::post('/contact', [ContactController::class, 'send']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ------------------- Password Reset Routes -------------------
Route::post('/password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [NewPasswordController::class, 'store']);
Route::post('/password/reset', [PasswordResetController::class, 'store']);

// ------------------- Admin Routes -------------------
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // User management
    Route::get('/users', [UserManagementController::class, 'index']);
    Route::put('/users/{id}/upgrade', [UserManagementController::class, 'upgradeToFarmer']);
    Route::put('/users/{id}/downgrade', [UserManagementController::class, 'downgradeToConsumer']);
    Route::put('/users/{id}/block', [UserManagementController::class, 'blockUser']);
    Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);
    Route::post('/users/{id}/stripe-connect', [PaymentController::class, 'onboardFarmer']);

    // Dashboard
    Route::get('/dashboard-stats', [AdminController::class, 'dashboardStats']);

    // Product management
    Route::get('/products', [ProductManagementController::class, 'index']);
    Route::post('/products', [ProductManagementController::class, 'store']);
    Route::put('/products/{id}', [ProductManagementController::class, 'update']);
    Route::delete('/products/{id}', [ProductManagementController::class, 'destroy']);

    // Order Management
    Route::get('/orders', [OrderManagementController::class, 'index']);
    Route::get('/orders/{id}', [OrderManagementController::class, 'show']);
    Route::put('/orders/{id}', [OrderManagementController::class, 'update']);
    Route::delete('/orders/{id}', [OrderManagementController::class, 'destroy']);

    // Feedback Management
    Route::get('/feedback', [FeedbackManagementController::class, 'index']);
    Route::delete('/feedback/{id}', [FeedbackManagementController::class, 'destroy']);
    
    // Admin Feedback Moderation (with isAdmin middleware)
        Route::get('/feedbacks', [FeedbackController::class, 'index']);
        Route::post('/feedbacks/{id}/approve', [FeedbackController::class, 'approve']);
        Route::post('/feedbacks/{id}/reply', [FeedbackController::class, 'reply']);
        Route::delete('/feedbacks/{id}', [FeedbackController::class, 'destroy']);
    });

Route::middleware('auth:sanctum')
    ->prefix('admin')
    ->group(function () {

        Route::get('/feedbacks', [FeedbackManagementController::class, 'index']);

        Route::post('/feedbacks/{id}/approve', [
            FeedbackManagementController::class,
            'approve'
        ]);

        Route::delete('/feedbacks/{id}', [
            FeedbackManagementController::class,
            'destroy'
        ]);
    });

    
Route::middleware('auth:sanctum')
    ->prefix('admin')
    ->group(function () {

        Route::get('/products', [ProductManagementController::class, 'index']);

        Route::put('/products/{id}', [ProductManagementController::class, 'update']);

        Route::delete('/products/{id}', [ProductManagementController::class, 'destroy']);
    });
// ------------------- Admin Notification Routes -------------------
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/notifications/unread-count', [\App\Http\Controllers\Admin\NotificationController::class, 'unreadCount']);
    Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead']);
});

// ------------------- Farmer Routes -------------------
Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/farmer/dashboard', fn() => response()->json(['message' => 'Welcome to Farmer Dashboard']));
    
    // Products
    Route::get('/farmer/products', [ProductController::class, 'index']);
    Route::post('/farmer/products', [ProductController::class, 'store']);
    Route::get('/farmer/products/{id}', [ProductController::class, 'show']);
    Route::put('/farmer/products/{id}', [ProductController::class, 'update']);
    Route::delete('/farmer/products/{id}', [ProductController::class, 'destroy']);
  
Route::get('/farmer/orders', [OrderController::class, 'farmerProductOrders']);   
 Route::post('/farmer/orders/{id}/ship', [OrderController::class, 'markAsShipped']);
    Route::post('/farmer/orders/{id}/deliver', [OrderController::class, 'markAsDelivered']);
    Route::post('/farmer/orders/{order}/status', [OrderController::class, 'updateStatus']);
Route::get('/farmer/sales-history', [SalesController::class, 'salesHistory']);
Route::middleware('auth:sanctum')->get('/farmer/sales-history', [OrderController::class, 'salesHistory']);


    // Feedback Moderation
    Route::post('/feedback/{id}/approve', [FeedbackController::class, 'approve']);
    Route::post('/feedback/{id}/reject', [FeedbackController::class, 'reject']);
    Route::post('/feedback/{id}/reply', [FeedbackController::class, 'reply']);
    Route::post('/reviews/{id}/approve', [FeedbackController::class, 'approve']);
});
// For farmers/consumers
Route::delete('/feedbacks/{feedback}', [FeedbackController::class, 'destroy']);

// For admins
Route::prefix('admin')->group(function () {
    Route::delete('/feedbacks/{id}', [FeedbackController::class, 'destroy']);
});
// ------------------- Consumer Routes -------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/consumer/dashboard', fn() => response()->json(['message' => 'Welcome to Consumer Dashboard']));

    // Cart
    Route::get('/consumer/cart', [CartController::class, 'index']);
    Route::post('/consumer/cart', [CartController::class, 'store']);
    Route::put('/consumer/cart/{id}', [CartController::class, 'update']);
    Route::delete('/consumer/cart/clear', [CartController::class, 'clear']);
    Route::delete('/consumer/cart/{id}', [CartController::class, 'destroy']);
    Route::get('/cart/total', [CartController::class, 'getTotal']);

    // Orders
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'userOrders']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

    // Product Feed
    Route::get('/products', [ProductController::class, 'feed']);
    Route::get('/feed', [ProductController::class, 'feed']);
    Route::get('/market/feed', [ProductController::class, 'feed']);
    Route::get('/products/search', [ProductController::class, 'search']);
});

// ------------------- Feedback Routes -------------------
// Public routes
Route::get('/feedbacks/approved', [FeedbackController::class, 'allApproved']);

// Authenticated user feedback CRUD
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/feedbacks', [FeedbackController::class, 'store']);
    Route::put('/feedbacks/{feedback}', [FeedbackController::class, 'update']);
    Route::delete('/feedbacks/{feedback}', [FeedbackController::class, 'destroy']);
    Route::post('/feedback', [FeedbackController::class, 'store']);
    
    // Feedback queries
    Route::get('/feedbacks/product/{productId}', [FeedbackController::class, 'productFeedback']);
    Route::get('/feedbacks/farmer', [FeedbackController::class, 'farmerFeedback']);
    Route::get('/feedbacks/user', [FeedbackController::class, 'userReviews']);
});

// ------------------- Messaging Routes -------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'start']);
    Route::get('/conversations/{id}', [ConversationController::class, 'show']);
    Route::delete('/conversations/{id}', [ConversationController::class, 'destroy']);
    Route::post('/conversations/{conversationId}/read', [MessageController::class, 'markAsRead']);
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::delete('/messages/{id}', [MessageController::class, 'destroy']);
});

// ------------------- Category & Unit Routes -------------------
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);

Route::get('/units', function () {
    return \App\Models\Unit::all();
});

// ------------------- Payment Routes -------------------
// Webhooks (public)
Route::post('/webhook', [PaymentController::class, 'handleWebhook']);

// Authenticated payment routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::post('/create-checkout-session', [PaymentController::class, 'createCheckoutSession']);
    Route::post('/confirm-payment', [PaymentController::class, 'confirmPayment']);
    Route::get('/checkout-session/{session_id}', [PaymentController::class, 'getCheckoutSession']);
});

// Public payment routes
Route::get('/payment/verify', function (Request $request) {
    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    
    try {
        $session = \Stripe\Checkout\Session::retrieve($request->query('session_id'));
        
        return response()->json([
            'success' => true,
            'paid' => $session->payment_status === 'paid',
            'order_id' => $session->metadata->order_id ?? null,
            'session' => $session
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/payment-success', [PaymentController::class, 'success']);

// Stripe onboarding (auth middleware without sanctum)
Route::middleware('auth')->group(function () {
    Route::get('/stripe/onboard/{user}', [PaymentController::class, 'onboardFarmer']);
    Route::post('/stripe/checkout', [PaymentController::class, 'createCheckoutSession']);
});

// Stripe success/refresh routes
Route::get('/farmer/onboarding/success', function(Request $request) {
    $user = auth()->user();
    if ($user && $user->stripe_user_id) {
        $user->stripe_connected = true;
        $user->save();
    }
    return redirect('/dashboard')->with('success', 'Your Stripe account is connected!');
});

Route::get('/stripe/refresh', function() {
    return redirect()->back()->with('message', 'Please try connecting Stripe again.');
})->name('stripe.refresh');

Route::get('/stripe/success', function() {
    return redirect()->back()->with('message', 'Stripe account connected successfully!');
})->name('stripe.success');