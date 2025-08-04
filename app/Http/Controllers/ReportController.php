<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{

    public function topSelling(Request $request)
    {
        // Get all order items joined with products, categories, brands, and orders
        $query = \App\Models\OrderItem::with(['product.category', 'product.brand', 'order']);

        // Exclude soft-deleted products
        $query->whereHas('product', function($q) {
            $q->whereNull('deleted_at');
        });

        // Date filter (by order created_at)
        if ($request->filled('start_date')) {
            $query->whereHas('order', function($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->start_date);
            });
        }
        if ($request->filled('end_date')) {
            $query->whereHas('order', function($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->end_date);
            });
        }

        // Search filter (by product name, part number, category, brand)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('part_number', 'like', "%{$search}%")
                  ->orWhereHas('category', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('brand', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orderItems = $query->orderByDesc('created_at')->paginate(10);

        // Individual sales records (each order item)
        $salesRecords = $orderItems;

        // Product totals
        $productTotals = $orderItems->groupBy('product_id')->map(function ($records) {
            return [
                'product' => $records->first()->product,
                'total_sold' => $records->sum('quantity'),
                'total_amount' => $records->sum(function($item) { return $item->quantity * $item->price; })
            ];
        })->sortByDesc('total_sold');

        $totalSales = $productTotals->sum('total_sold');
        $totalAmount = $productTotals->sum('total_amount');

        return view('reports.top-selling', compact('salesRecords', 'productTotals', 'totalSales', 'totalAmount'));
    }

    public function salesByPeriod(Request $request)
    {
        $period = $request->get('period', 'daily'); // Default to daily
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $paymentMethod = $request->get('payment_method');

        // Get all order items joined with orders and products
        $query = \App\Models\OrderItem::with(['order', 'product']);
        
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

        // Group by period (day or month) and aggregate payment method columns
        $salesData = $orderItems->groupBy(function($item) use ($period) {
            $date = $item->order->created_at;
            return $period === 'monthly' ? $date->format('Y-m') : $date->format('Y-m-d');
        })->map(function($items, $date) {
            $cashAmount = $items->filter(function($item) {
                return $item->order->payment_method === 'cash';
            })->sum(function($item) { return $item->quantity * $item->price; });
            $tngAmount = $items->filter(function($item) {
                return $item->order->payment_method === 'tng_wallet';
            })->sum(function($item) { return $item->quantity * $item->price; });
            $cardAmount = $items->filter(function($item) {
                return $item->order->payment_method === 'bank';
            })->sum(function($item) { return $item->quantity * $item->price; });
            $bankTransferAmount = $items->filter(function($item) {
                return $item->order->payment_method === 'bank_transfer';
            })->sum(function($item) { return $item->quantity * $item->price; });
            $totalAmount = $items->sum(function($item) { return $item->quantity * $item->price; });
            return (object) [
                'date' => $date,
                'cash_amount' => $cashAmount,
                'tng_amount' => $tngAmount,
                'card_amount' => $cardAmount,
                'bank_transfer_amount' => $bankTransferAmount,
                'total_amount' => $totalAmount
            ];
        })->sortBy('date');

        $totalAmount = $salesData->sum('total_amount');
        $cashTotal = $salesData->sum('cash_amount');
        $tngTotal = $salesData->sum('tng_amount');

        return view('reports.summary', compact('salesData', 'totalAmount', 'period', 'startDate', 'endDate', 'paymentMethod', 'cashTotal', 'tngTotal'));
    }

} 