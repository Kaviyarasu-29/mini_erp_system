<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('purchases.index');
});

// Masters
Route::prefix('masters')->name('masters.')->group(function () {

    // Categories
    Route::controller(CategoryController::class)->prefix('categories')->name('categories.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{category}', 'update')->name('update');
        Route::delete('/{category}', 'destroy')->name('destroy');
    });

    // Units
    Route::controller(UnitController::class)->prefix('units')->name('units.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{unit}', 'update')->name('update');
        Route::delete('/{unit}', 'destroy')->name('destroy');
    });

    // Taxes
    Route::controller(TaxController::class)->prefix('taxes')->name('taxes.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{tax}', 'update')->name('update');
        Route::delete('/{tax}', 'destroy')->name('destroy');
    });

    // Customers
    Route::controller(CustomerController::class)->prefix('customers')->name('customers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/new', 'new')->name('new');
        Route::post('/', 'store')->name('store');
        Route::get('/{customer}/edit', 'edit')->name('edit');
        Route::put('/{customer}', 'update')->name('update');
        Route::delete('/{customer}', 'destroy')->name('destroy');
    });

    // Suppliers
    Route::controller(SupplierController::class)->prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/new', 'new')->name('new');
        Route::post('/', 'store')->name('store');
        Route::get('/{supplier}/edit', 'edit')->name('edit');
        Route::put('/{supplier}', 'update')->name('update');
        Route::delete('/{supplier}', 'destroy')->name('destroy');
    });

    // Products
    Route::controller(ProductController::class)->prefix('products')->name('products.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/new', 'new')->name('new');
        Route::post('/', 'store')->name('store');
        Route::get('/{product}/edit', 'edit')->name('edit');
        Route::put('/{product}', 'update')->name('update');
        Route::delete('/{product}', 'destroy')->name('destroy');
    });
});

// Purchases
Route::controller(PurchaseController::class)->prefix('purchases')->name('purchases.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::get('/new', 'new')->name('new');
    Route::post('/', 'store')->name('store');
    Route::get('/{purchase}', 'show')->name('show');
    Route::get('/{purchase}/edit', 'edit')->name('edit');
    Route::put('/{purchase}', 'update')->name('update');
    Route::delete('/{purchase}', 'destroy')->name('destroy');
});

// Sales
Route::controller(SaleController::class)->prefix('sales')->name('sales.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::get('/new', 'new')->name('new');
    Route::get('/scan/{code}', 'scanBarcode')->name('scan');
    Route::post('/', 'store')->name('store');
    Route::get('/{sale}', 'show')->name('show');
    Route::get('/{sale}/edit', 'edit')->name('edit');
    Route::put('/{sale}', 'update')->name('update');
    Route::delete('/{sale}', 'destroy')->name('destroy');
});
