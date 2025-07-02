<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function topSelling(Request $request)
    {
        // First get individual sales records
        $query = Report::with(['product.category', 'product.brand'])
            ->select('product_id', 'quantity_sold', 'total_amount', 'created_at');

        // Apply date filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Apply search filter
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

        // Get individual sales records
        $salesRecords = $query->orderBy('created_at', 'desc')->get();

        // Calculate totals for each product
        $productTotals = $salesRecords->groupBy('product_id')->map(function ($records) {
            return [
                'product' => $records->first()->product,
                'total_sold' => $records->sum('quantity_sold'),
                'total_amount' => $records->sum('total_amount')
            ];
        })->sortByDesc('total_sold');

        // Calculate overall totals
        $totalSales = $productTotals->sum('total_sold');
        $totalAmount = $productTotals->sum('total_amount');

        return view('reports.top-selling', compact('salesRecords', 'productTotals', 'totalSales', 'totalAmount'));
    }

    public function salesByPeriod(Request $request)
    {
        $period = $request->get('period', 'daily');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now());
        $paymentMethod = $request->get('payment_method');

        $query = Report::with('product')
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(quantity_sold) as total_sold'),
                DB::raw('SUM(total_amount) as total_amount'),
                'payment_method'
            )
            ->whereBetween('sale_date', [$startDate, $endDate]);

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($period === 'monthly') {
            $query->groupBy(DB::raw('YEAR(sale_date)'), DB::raw('MONTH(sale_date)'), 'payment_method');
        } else {
            $query->groupBy(DB::raw('DATE(sale_date)'), 'payment_method');
        }

        $salesData = $query->get();

        $totalSales = $salesData->sum('total_sold');
        $totalAmount = $salesData->sum('total_amount');

        return view('reports.summary', compact('salesData', 'totalSales', 'totalAmount', 'period', 'startDate', 'endDate', 'paymentMethod'));
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $product->quantity]
        ]);

        $quantity = $request->quantity;
        $totalAmount = $quantity * $product->selling_price;

        Report::create([
            'product_id' => $product->id,
            'quantity_sold' => $quantity,
            'total_amount' => $totalAmount,
            'sale_date' => now()
        ]);

        $product->quantity -= $quantity;
        $product->save();

        return redirect()->route('products.index')
            ->with('success', 'Product sold successfully!');
    }
} 