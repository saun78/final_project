<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TopSellingReportController extends Controller
{
    public function topSelling(Request $request)
    {
        // Get all order items joined with products, categories, brands, and orders
        $query = OrderItem::with(['product.category', 'product.brand', 'order']);

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

        $orderItems = $query->orderByDesc('created_at')->get();

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

    public function exportPdf(Request $request)
    {
        $query = \App\Models\OrderItem::with(['product.category', 'product.brand', 'order']);

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

        $orderItems = $query->orderByDesc('created_at')->get();
        $salesRecords = $orderItems;
        $productTotals = $orderItems->groupBy('product_id')->map(function ($records) {
            return [
                'product' => $records->first()->product,
                'total_sold' => $records->sum('quantity'),
                'total_amount' => $records->sum(function($item) { return $item->quantity * $item->price; })
            ];
        })->sortByDesc('total_sold');
        $totalSales = $productTotals->sum('total_sold');
        $totalAmount = $productTotals->sum('total_amount');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.top-selling', compact('salesRecords', 'productTotals', 'totalSales', 'totalAmount'));
        return $pdf->download('top-selling-report.pdf');
    }
} 