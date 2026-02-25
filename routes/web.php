<?php

use App\Http\Controllers\Import\UserImportController;
use App\Http\Controllers\JurnalPrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/faq', function () {
    return view('faq');
})->name('faq');


Route::middleware(['auth'])->group(function () {
    Route::get('/jurnal/{record}/print', [JurnalPrintController::class, 'printSingle'])->name('jurnal.print');
    Route::get('/jurnal/print-bulk', [JurnalPrintController::class, 'printBulk'])->name('jurnal.print.bulk');
});
