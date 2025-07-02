<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Public root route - redirect based on authentication status
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Product Routes
    Route::resource('products', ProductController::class);
    
    // Batch Inventory Routes
    Route::get('/products/{product}/stock-in', [ProductController::class, 'stockInForm'])->name('products.stock-in.form');
    Route::post('/products/{product}/stock-in', [ProductController::class, 'stockIn'])->name('products.stock-in');
    Route::get('/products/{product}/batches', [ProductController::class, 'batches'])->name('products.batches');
    Route::put('/products/{product}/selling-price', [ProductController::class, 'updateSellingPrice'])->name('products.update-selling-price');
     
    // Category Routes
    Route::resource('categories', CategoryController::class);
    
    // Brand Routes
    Route::resource('brands', BrandController::class);
    
    // Supplier Routes
    Route::resource('suppliers', SupplierController::class);
    
    // Order/Receipt Routes
    Route::resource('orders', OrderController::class);
    Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
    Route::get('/orders/{order}/print-pdf', [OrderController::class, 'printSingle'])->name('orders.print.pdf');
    Route::get('/orders/export/pdf', [OrderController::class, 'exportPdf'])->name('orders.export.pdf');
    Route::get('/orders/export/excel', [OrderController::class, 'exportExcel'])->name('orders.export.excel');

    // Reports Routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/top-selling', [ReportController::class, 'topSelling'])->name('reports.top-selling');
    Route::get('/reports/summary', [ReportController::class, 'salesByPeriod'])->name('reports.summary');
    
});