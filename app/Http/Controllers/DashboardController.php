<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\OrderItem;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 統計數據
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalBrands = Brand::count();
        
        // 庫存統計
        $lowStockProducts = Product::where('quantity', '>', 0)->where('quantity', '<=', 10)->count();
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

        // 缺貨產品
        $outOfStockItems = Product::with(['category', 'brand'])
            ->where('quantity', 0)
            ->orderBy('name', 'asc')
            ->limit(5)
            ->get();

        // Top Selling Data for last 7 days
        $topSellingData = $this->getTopSellingData();

        // Summary Data for last 7 days
        $summaryData = $this->getSummaryData();

        // Calculate total amount for last 7 days
        $totalAmount = array_sum(array_column($summaryData, 'total_amount'));

        // Profit Data for last 7 days
        $profitData = $this->getProfitData();

        // 最近10笔当天的库存异动（只显示in和out）
        $today = now()->toDateString();
        $recentMovements = \App\Models\InventoryMovement::with(['product.supplier'])
            ->whereDate('movement_date', $today)
            ->whereIn('movement_type', ['stock_in', 'sale', 'stock_out'])
            ->orderBy('movement_date', 'desc')
            ->paginate(10);

        // Top 5 selling products in the current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $productTotals = OrderItem::with('product', 'order')
            ->whereHas('order', function($q) use ($startOfMonth, $endOfMonth) {
                $q->whereDate('created_at', '>=', $startOfMonth)
                  ->whereDate('created_at', '<=', $endOfMonth);
            })
            ->get()
            ->groupBy('product_id')
            ->map(function ($records) {
                return [
                    'name' => $records->first()->product->name ?? 'Unknown',
                    'total_sold' => $records->sum('quantity'),
                    'total_amount' => $records->sum(function($item) { return $item->quantity * $item->price; }),
                ];
            })
            ->filter(function($item) {
                return $item['total_sold'] > 0;
            })
            ->sortByDesc('total_sold')
            ->take(5)
            ->values()
            ->all();

            $summaryLabels = [];
            $summaryAmounts = [];

        foreach ($summaryData as $date => $data) {
            $summaryLabels[] = Carbon::parse($date)->format('M d'); // eg: "Jul 17"
            $summaryAmounts[] = $data['total_amount'];
        }


        return view('dashboard', compact(
            'totalProducts',
            'totalCategories',
            'totalBrands',
            'lowStockProducts',
            'outOfStockProducts',
            'totalValue',
            'recentProducts',
            'lowStockItems',
            'outOfStockItems',
            'topSellingData',
            'summaryData',
            'profitData',
            'recentMovements',
            'totalAmount',
            'productTotals',
            'summaryLabels',      // ✅ 新加
            'summaryAmounts'      // ✅ 新加
        ));
    }

    private function getTopSellingData()
    {
        // Get the last 7 days
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(6);
        
        // Get daily top selling products
        $dailyTopSelling = OrderItem::with(['product', 'order'])
            ->whereHas('order', function($query) use ($startDate, $endDate) {
                $query->whereDate('created_at', '>=', $startDate)
                      ->whereDate('created_at', '<=', $endDate);
            })
            ->get()
            ->groupBy(function($item) {
                return $item->order->created_at->format('Y-m-d');
            })
            ->map(function($dayItems) {
                return $dayItems->groupBy('product_id')
                    ->map(function($productItems) {
                        return [
                            'product_name' => $productItems->first()->product->name,
                            'total_sold' => $productItems->sum('quantity'),
                            'total_amount' => $productItems->sum(function($item) {
                                return $item->quantity * $item->price;
                            })
                        ];
                    })
                    ->sortByDesc('total_sold')
                    ->take(5) // Top 5 products per day
                    ->values();
            });

        // Fill in missing days with empty data
        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $result[$date] = $dailyTopSelling->get($date, []);
        }

        return $result;
    }

    private function getSummaryData()
    {
        // Get the last 7 days
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(6);
        
        // Get daily summary data
        $dailySummary = OrderItem::with(['order'])
            ->whereHas('order', function($query) use ($startDate, $endDate) {
                $query->whereDate('created_at', '>=', $startDate)
                      ->whereDate('created_at', '<=', $endDate);
            })
            ->get()
            ->groupBy(function($item) {
                return $item->order->created_at->format('Y-m-d');
            })
            ->map(function($dayItems) {
                $totalAmount = $dayItems->sum(function($item) {
                    return $item->quantity * $item->price;
                });
                
                $cashAmount = $dayItems->filter(function($item) {
                    return $item->order->payment_method === 'cash';
                })->sum(function($item) {
                    return $item->quantity * $item->price;
                });
                
                $tngAmount = $dayItems->filter(function($item) {
                    return $item->order->payment_method === 'tng_wallet';
                })->sum(function($item) {
                    return $item->quantity * $item->price;
                });
                
                $cardAmount = $dayItems->filter(function($item) {
                    return $item->order->payment_method === 'bank';
                })->sum(function($item) {
                    return $item->quantity * $item->price;
                });
                
                return [
                    'total_amount' => $totalAmount,
                    'cash_amount' => $cashAmount,
                    'tng_amount' => $tngAmount,
                    'card_amount' => $cardAmount,
                    'total_orders' => $dayItems->groupBy('order_id')->count()
                ];
            });

        // Fill in missing days with empty data
        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $result[$date] = $dailySummary->get($date, [
                'total_amount' => 0,
                'cash_amount' => 0,
                'tng_amount' => 0,
                'card_amount' => 0,
                'total_orders' => 0
            ]);
        }

        return $result;
    }

    private function getProfitData()
    {
        // Get the last 7 days
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(6);
        
        // Get daily profit data
        $dailyProfit = OrderItem::with(['order'])
            ->whereHas('order', function($query) use ($startDate, $endDate) {
                $query->whereDate('created_at', '>=', $startDate)
                      ->whereDate('created_at', '<=', $endDate);
            })
            ->get()
            ->groupBy(function($item) {
                return $item->order->created_at->format('Y-m-d');
            })
            ->map(function($dayItems) {
                $totalSales = $dayItems->sum(function($item) {
                    return $item->quantity * $item->price;
                });
                
                $totalCogs = $dayItems->sum(function($item) {
                    return $item->quantity * $item->cost_price;
                });
                
                $totalProfit = $dayItems->sum(function($item) {
                    return ($item->price - $item->cost_price) * $item->quantity;
                });
                
                return [
                    'total_sales' => $totalSales,
                    'total_cogs' => $totalCogs,
                    'total_profit' => $totalProfit,
                    'profit_margin' => $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0
                ];
            });

        // Fill in missing days with empty data
        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $result[$date] = $dailyProfit->get($date, [
                'total_sales' => 0,
                'total_cogs' => 0,
                'total_profit' => 0,
                'profit_margin' => 0
            ]);
        }

        return $result;
    }
} 