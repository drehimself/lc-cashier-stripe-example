<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::middleware(['auth:sanctum', 'verified', 'nonPayingCustomer'])->get('/subscribe', function () {
    return view('subscribe', [
        'intent' => auth()->user()->createSetupIntent(),
    ]);
})->name('subscribe');

Route::middleware(['auth:sanctum', 'verified', 'nonPayingCustomer'])->post('/subscribe', function (Request $request) {
    // dd($request->all());
    auth()->user()->newSubscription('cashier', $request->plan)->create($request->paymentMethod);

    return redirect('/dashboard');
})->name('subscribe.post');

Route::middleware(['auth:sanctum', 'verified', 'payingCustomer'])->get('/members', function () {
    return view('members');
})->name('members');

Route::middleware(['auth:sanctum', 'verified'])->get('/charge', function () {
    return view('charge');
})->name('charge');

Route::middleware(['auth:sanctum', 'verified'])->post('/charge', function (Request $request) {
    // dd($request->all());
    // auth()->user()->charge(1000, $request->paymentMethod);
    auth()->user()->createAsStripeCustomer();
    auth()->user()->updateDefaultPaymentMethod($request->paymentMethod);
    auth()->user()->invoiceFor('One Time Fee', 1500);

    return redirect('/dashboard');
})->name('charge.post');

Route::middleware(['auth:sanctum', 'verified'])->get('/invoices', function () {
    return view('invoices', [
        'invoices' => auth()->user()->invoices(),
    ]);
})->name('invoices');

Route::get('user/invoice/{invoice}', function (Request $request, $invoiceId) {
    return $request->user()->downloadInvoice($invoiceId, [
        'vendor' => 'Your Company',
        'product' => 'Your Product',
    ]);
});

Route::post(
    'stripe/webhook',
    [WebhookController::class, 'handleWebhook']
);
