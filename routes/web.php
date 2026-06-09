<?php

use Illuminate\Support\Facades\Route;
use App\Models\Result;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/', function () {
    return view('welcome');
});

// Result print route
Route::get('/results/{result}/print', function ($resultId) {
    $result = Result::with(['student', 'exam', 'subject'])->findOrFail($resultId);
    $pdf = Pdf::loadView('pdf.result_slip', ['result' => $result]);
    return $pdf->download('result_slip_' . $result->student->admission_number . '.pdf');
})->name('results.print')->middleware(['auth']);