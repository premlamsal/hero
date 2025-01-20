<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/register', [AuthController::class, 'register']);



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// units routes
Route::get('/units', [UnitController::class, 'index']);

Route::get('/units/{id}', [UnitController::class, 'show']);

Route::put('/units/{id}', [UnitController::class, 'update']);

Route::delete('/units/{id}', [UnitController::class, 'destroy']);

Route::post('/units', [UnitController::class, 'store']);


// categories routes
Route::get('/categories', [CategoryController::class, 'index']);

Route::get('/categories/{id}', [CategoryController::class, 'show']);

Route::put('/categories/{id}', [CategoryController::class, 'update']);

Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

Route::post('/categories', [CategoryController::class, 'store']);






// customers routes
Route::get('/customers', [CustomerController::class, 'index']);

Route::get('/customers/{id}', [CustomerController::class, 'show']);

Route::put('/customers/{id}', [CustomerController::class, 'update']);

Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);

Route::post('/customers', [CustomerController::class, 'store']);


// suppliers routes
Route::get('/suppliers', [SupplierController::class, 'index']);

Route::get('/suppliers/{id}', [SupplierController::class, 'show']);

Route::put('/suppliers/{id}', [SupplierController::class, 'update']);

Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy']);

Route::post('/suppliers', [SupplierController::class, 'store']);


//product

Route::get('/products', [ProductController::class, 'index']);

Route::get('/products/{id}', [ProductController::class, 'show']);

Route::put('/products/{id}', [ProductController::class, 'update']);

Route::delete('/products/{id}', [ProductController::class, 'destroy']);

Route::post('/products', [ProductController::class, 'store']);


//sale
Route::apiResource('sales', SaleController::class);

//purchase
Route::apiResource('purchases', PurchaseController::class);



Route::prefix('products/{productId}/images')->group(function () {
    Route::get('/', [ProductImageController::class, 'index']);
    Route::post('/upload', [ProductImageController::class, 'upload']);
    Route::delete('/{imageId}', [ProductImageController::class, 'destroy']);
});
