<?php

use App\Http\Controllers\HistoryController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect('admin/login');
});

// routes/web.php
Route::get('/invoice/sales/{transaksi}', [InvoiceController::class, 'sales'])->name('invoice.sales_preview');
Route::get('/invoice/service/{transaksi}', [InvoiceController::class, 'service'])->name('invoice.service_preview');
Route::get('/history/service/{transaksi}', [HistoryController::class, 'service'])->name('service.history');


Route::get('/sales-invoice-download/{filename}', function ($filename) {
    $path = storage_path("app/invoices/sales/{$filename}");

    abort_unless(file_exists($path), 404);

    return response()->file($path);
})->name('sales.invoice.download');

Route::get('/service-invoice-download/{filename}', function ($filename) {
    $path = storage_path("app/invoices/service/{$filename}");

    abort_unless(file_exists($path), 404);

    return response()->file($path);
})->name('service.invoice.download');

// Route::get('/', function () {
//     return view('welcome');
// });
