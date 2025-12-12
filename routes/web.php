<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/payment-success', function () {
    return view('payment-success'); // simple blade or redirect to SPA root
});

Route::get('/payment-cancel', function () {
    return view('payment-cancel');
});
use App\Models\User;
use Stripe\Stripe;
use Stripe\Account;
use Stripe\AccountLink;

Route::get('/onboard-farmer/{id}', function ($id) {
    Stripe::setApiKey(config('services.stripe.secret'));

    $user = User::findOrFail($id);

    // If they don’t have a Stripe account yet, create one
    if (!$user->stripe_account_id) {
        $account = Account::create([
            'type' => 'express',
            'country' => 'FR',
            'email' => $user->email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
        ]);
        $user->stripe_account_id = $account->id;
        $user->save();
    }

    // Generate AccountLink URL
    $accountLink = AccountLink::create([
        'account' => $user->stripe_account_id,
        'refresh_url' => url("/onboard-farmer/{$id}"),
        'return_url' => url('/farmer/dashboard'),
        'type' => 'account_onboarding',
    ]);

    return redirect($accountLink->url);
});