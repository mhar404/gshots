<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Models\Product;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});

Route::get('/products', function () {
    $products = Product::all();

    // Add full URL for each product image
    $products->transform(function ($product) {
        if ($product->image) {
            $product->image = asset('storage/' . $product->image);
        } else {
            $product->image = asset('storage/images/default.png'); // fallback
        }
        return $product;
    });

    return response()->json($products);
});