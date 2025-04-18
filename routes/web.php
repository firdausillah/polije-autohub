<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect('admin/login');
});

// routes/web.php
// Route::get('/invoice/{transaksi}', [InvoiceController::class, 'preview'])->name('invoice.preview');


Route::get('/penjualan-invoice-download/{filename}', function ($filename) {
    $path = storage_path("app/invoices/penjualan/{$filename}");

    abort_unless(file_exists($path), 404);

    return response()->file($path);
})->name('penjualan.invoice.download');

Route::get('/service-invoice-download/{filename}', function ($filename) {
    $path = storage_path("app/invoices/pelayanan-service/{$filename}");

    abort_unless(file_exists($path), 404);

    return response()->file($path);
})->name('service.invoice.download');

// Route::get('/', function () {
//     return view('welcome');
// });
