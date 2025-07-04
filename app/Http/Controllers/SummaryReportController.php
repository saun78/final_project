<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
                return $item->order->payment_method === 'card';
            })->sum(function($item) { return $item->quantity * $item->price; });
            $totalAmount = $items->sum(function($item) { return $item->quantity * $item->price; });
            return (object) [
                'date' => $date,
                'cash_amount' => $cashAmount,
                'tng_amount' => $tngAmount,
                'card_amount' => $cardAmount,
                'total_amount' => $totalAmount
            ];
        })->sortBy('date');

        $totalAmount = $salesData->sum('total_amount');
        $cashTotal = $salesData->sum('cash_amount');
        $tngTotal = $salesData->sum('tng_amount');

        return view('reports.summary', compact('salesData', 'totalAmount', 'period', 'startDate', 'endDate', 'paymentMethod', 'cashTotal', 'tngTotal'));
    }
} 