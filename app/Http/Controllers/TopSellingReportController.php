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

        $orderItems = $query->orderByDesc('created_at')->get();

        // Group by time (to minute) and product_id
        $groupedSales = $orderItems->groupBy(function($item) {
            return $item->order->created_at->format('Y-m-d H:i') . '-' . $item->product_id;
        })->map(function($group) {
            $first = $group->first();
            return (object) [
                'datetime' => $first->order->created_at->format('Y-m-d H:i'),
                'product' => $first->product,
                'quantity' => $group->sum('quantity'),
                'amount' => $group->sum(function($item) { return $item->quantity * $item->price; }),
                'category' => $first->product?->category,
                'brand' => $first->product?->brand,
            ];
        });

        // Paginate manually
        $perPage = 10;
        $currentPage = $request->input('page', 1);
        $pagedSales = $groupedSales->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $salesRecords = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedSales,
            $groupedSales->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

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

        // Exclude soft-deleted products
        $query->whereHas('product', function($q) {
            $q->whereNull('deleted_at');
        });

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