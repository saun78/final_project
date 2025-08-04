<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProfitReportController extends Controller
{
    public function profit(Request $request)
    {
        $period = $request->get('period', 'daily');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $paymentMethod = $request->get('payment_method');

        $query = OrderItem::with(['order', 'product']);
        
        // Exclude soft-deleted products
        $query->whereHas('product', function($q) {
            $q->whereNull('deleted_at');
        });
        
        $query->whereHas('order', function($q) use ($startDate, $endDate, $paymentMethod) {
            if ($startDate && $endDate) {
                $q->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
            } elseif ($startDate) {
                $q->whereDate('created_at', '>=', $startDate);
            } elseif ($endDate) {
                $q->whereDate('created_at', '<=', $endDate);
            }
            if ($paymentMethod) {
                $q->where('payment_method', $paymentMethod);
            }
        });

        $orderItems = $query->get();

        // Group by period, then by product
        $profitData = $orderItems->groupBy(function($item) use ($period) {
            $date = $item->order->created_at;
            return $period === 'monthly' ? $date->format('Y-m') : $date->format('Y-m-d');
        })->map(function($items, $date) {
            $products = $items->groupBy('product_id')->map(function($productItems) {
                $product = $productItems->first()->product;
                $sales = $productItems->sum(function($item) { return $item->quantity * $item->price; });
                $cogs = $productItems->sum(function($item) { return $item->quantity * $item->cost_price; });
                $profit = $productItems->sum(function($item) { return ($item->price - $item->cost_price) * $item->quantity; });
                $quantity = $productItems->sum('quantity');
                return (object) [
                    'product_name' => $product ? $product->name : 'Unknown',
                    'quantity' => $quantity,
                    'sales' => $sales,
                    'cogs' => $cogs,
                    'profit' => $profit
                ];
            });
            $sales = $products->sum('sales');
            $cogs = $products->sum('cogs');
            $profit = $products->sum('profit');
            return (object) [
                'date' => $date,
                'products' => $products,
                'sales' => $sales,
                'cogs' => $cogs,
                'profit' => $profit
            ];
        })->sortBy('date');

        $profitData = $profitData->sortByDesc('date');

        // Paginate profitData (10 per page)
        $perPage = 10;
        $page = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $pagedData = $profitData->slice(($page - 1) * $perPage, $perPage)->values();
        $profitData = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            $profitData->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $totalSales = $profitData->sum('sales');
        $totalCogs = $profitData->sum('cogs');
        $totalProfit = $profitData->sum('profit');

        return view('reports.profit', compact('profitData', 'totalSales', 'totalCogs', 'totalProfit', 'period', 'startDate', 'endDate', 'paymentMethod'));
    }
} 