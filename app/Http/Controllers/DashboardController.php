<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class DashboardController extends Controller
{
    public function index()
    {
        // 統計數據
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalBrands = Brand::count();
        
        // 庫存統計
        $lowStockProducts = Product::where('quantity', '<=', 10)->count();
        $outOfStockProducts = Product::where('quantity', 0)->count();
        $totalValue = Product::sum(DB::raw('quantity * purchase_price'));
        
        // 最新產品
        $recentProducts = Product::with(['category', 'brand'])
            ->latest()
            ->limit(5)
            ->get();
            
        // 低庫存產品
        $lowStockItems = Product::with(['category', 'brand'])
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', 10)
            ->orderBy('quantity', 'asc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalProducts',
            'totalCategories', 
            'totalBrands',
            'lowStockProducts',
            'outOfStockProducts',
            'totalValue',
            'recentProducts',
            'lowStockItems'
        ));
    }
} 