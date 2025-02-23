<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('admin/login');
});

// Route::get('/', function () {
//     return view('welcome');
// });
