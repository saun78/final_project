<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ManageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('dashboard');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Product Routes
    Route::resource('products', ProductController::class);
    
    // Category Routes
    Route::resource('categories', CategoryController::class);
    
    // Brand Routes
    Route::resource('brands', BrandController::class);
    
    // Reports Routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/top-selling', [ReportController::class, 'topSelling'])->name('reports.top-selling');
    Route::get('/reports/sales-by-period', [ReportController::class, 'salesByPeriod'])->name('reports.sales-by-period');
    
    // Order/Receipt Routes
    Route::resource('orders', OrderController::class);
    Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
    Route::get('/orders/{order}/print-pdf', [OrderController::class, 'printSingle'])->name('orders.print.pdf');
    Route::get('/orders/export/pdf', [OrderController::class, 'exportPdf'])->name('orders.export.pdf');
    Route::get('/orders/export/excel', [OrderController::class, 'exportExcel'])->name('orders.export.excel');
    
    // Manage Categories & Brands Routes
    Route::get('/manage', [ManageController::class, 'index'])->name('manage.index');
    Route::post('/manage/category', [ManageController::class, 'storeCategory'])->name('manage.category.store');
    Route::put('/manage/category/{category}', [ManageController::class, 'updateCategory'])->name('manage.category.update');
    Route::delete('/manage/category/{category}', [ManageController::class, 'destroyCategory'])->name('manage.category.destroy');
    Route::post('/manage/brand', [ManageController::class, 'storeBrand'])->name('manage.brand.store');
    Route::put('/manage/brand/{brand}', [ManageController::class, 'updateBrand'])->name('manage.brand.update');
    Route::delete('/manage/brand/{brand}', [ManageController::class, 'destroyBrand'])->name('manage.brand.destroy');
});
