<?php

use App\Http\Controllers\API\ProductCategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserController;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('products', [ProductController::class, 'all']);
Route::get('categories', [ProductCategoryController::class, 'all']);

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [UserController::class, 'fetch']);
    Route::post('user', [UserController::class, 'updateProfile']);
    Route::post('logout', [UserController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'role:USERS'])->group(function () {
    Route::get('transaction', [TransactionController::class, 'all']);
    Route::post('checkout', [TransactionController::class, 'checkout']);
    Route::post('transaction/paid', [TransactionController::class, 'paid']);
    Route::get('payment-method', [TransactionController::class, 'paymentMehtod']);
});

Route::middleware(['auth:sanctum', 'role:ADMIN'])->group(function () {
    Route::get('admin/products/{id}', [ProductController::class, 'detail']);
    Route::post('admin/products', [ProductController::class, 'create']);
    Route::post('admin/products/{id}', [ProductController::class, 'update']);
    Route::delete('admin/products/{id}', [ProductController::class, 'delete']);
});
