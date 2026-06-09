<?php

use App\Http\Controllers\PrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Print routes
Route::middleware(['auth'])->group(function () {
    Route::get('/invoices/{id}/print', [PrintController::class, 'printInvoice'])->name('invoices.print');
    Route::get('/payments/{id}/receipt', [PrintController::class, 'printReceipt'])->name('payments.receipt');
});