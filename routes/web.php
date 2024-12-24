<?php

use App\Filament\Resources\PaymentResource\Pages\PaymentPage;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('role:patient,admin')->group( function () {
// Route::get('filament/admin/resources/payments/stripe/{payment}', [StripeController::class, 'index'])->name('stripe.checkout');
// Route::post('filament/admin/resources/payments/stripe/create-charge/{payment}', [StripeController::class, 'createCharge'])->name('stripe.create-charge');
Route::post('filament/admin/payments/stripe/create-charge/{payment}', [PaymentPage::class, 'createCharge'])->name('stripe.create-charge');
});
//filament-docbook.test/admin/payments/stripe
