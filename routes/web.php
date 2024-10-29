<?php

use App\Filament\Resources\ProductResource\Pages\ListProducts;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('products', ListProducts::class);