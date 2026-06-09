<?php

use App\Http\Controllers\PrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Print routes
Route::middleware(['auth'])->group(function () {
    // Invoice and Receipt
    Route::get('/invoices/{id}/print', [PrintController::class, 'printInvoice'])->name('invoices.print');
    Route::get('/payments/{id}/receipt', [PrintController::class, 'printReceipt'])->name('payments.receipt');
    
    // Fee Structure routes
    Route::get('/fee-structures/{id}/print', [PrintController::class, 'printFeeStructure'])->name('fee-structures.print');
    Route::get('/fee-structures/print-all', [PrintController::class, 'printAllFeeStructures'])->name('fee-structures.print-all');
    Route::get('/fee-structures/print-selected', [PrintController::class, 'printSelectedFeeStructures'])->name('fee-structures.print-selected');
    Route::get('/fee-structures/print-by-grade', [PrintController::class, 'printFeeStructuresByGrade'])->name('fee-structures.print-by-grade');
});