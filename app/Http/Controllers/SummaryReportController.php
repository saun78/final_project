<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class SummaryReportController extends Controller
{
    public function summary(Request $request)
    {
        $period = $request->get('period', 'daily'); // Default to daily
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $paymentMethod = $request->get('payment_method');

        // Get all order items joined with orders and products
        $query = OrderItem::with(['order', 'product']);
        
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

            // Collect unique receipts for the day
            $receipts = $items->pluck('order')->unique('id')->map(function($order) {
                return [
                    'order_number' => $order->order_number,
                    'payment_method' => $order->payment_method,
                    'time' => $order->created_at->format('H:i'),
                    'total_amount' => $order->calculateTotal(),
                    'id' => $order->id, // keep id for linking
                ];
            })->values();

            return (object) [
                'date' => $date,
                'cash_amount' => $cashAmount,
                'tng_amount' => $tngAmount,
                'card_amount' => $cardAmount,
                'bank_transfer_amount' => $bankTransferAmount,
                'total_amount' => $totalAmount,
                'receipts' => $receipts,
            ];
        })->sortBy('date');

        $chartData = $salesData; // for chart (original order)
        $salesData = $salesData->sortByDesc('date'); // for table (descending)

        $totalAmount = $salesData->sum('total_amount');
        $cashTotal = $salesData->sum('cash_amount');
        $tngTotal = $salesData->sum('tng_amount');
        $cardTotal = $salesData->sum('card_amount');
        $bankTransferTotal = $salesData->sum('bank_transfer_amount');

        $salesData = $salesData->values(); // Ensure it's numerically indexed

        // Prepare data for chart
        $dates = $salesData->pluck('date')->toArray();
        $totals = $salesData->pluck('total_amount')->toArray();
        $cashAmounts = $salesData->pluck('cash_amount')->toArray();
        $tngAmounts = $salesData->pluck('tng_amount')->toArray();
        $cardAmounts = $salesData->pluck('card_amount')->toArray();
        $bankTransferAmounts = $salesData->pluck('bank_transfer_amount')->toArray();

        // Pagination
        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $pagedData = $salesData->slice(($page - 1) * $perPage, $perPage)->values();
        $salesData = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            $salesData->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('reports.summary', compact('salesData', 'chartData', 'totalAmount', 'period', 'startDate', 'endDate', 'paymentMethod', 'cashTotal', 'tngTotal', 'dates', 'totals', 'cashAmounts', 'tngAmounts', 'cardAmounts', 'bankTransferAmounts', 'cardTotal', 'bankTransferTotal'));
    }
} 