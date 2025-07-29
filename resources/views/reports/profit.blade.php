@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Profit Report</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.profit') }}" method="GET" class="mb-4">
                        <div class="row align-items-end g-2">
                            <div class="col-auto">
                                <label for="period" class="form-label">Report Period</label>
                                <select name="period" id="period" class="form-control">
                                    <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <label for="quick_range" class="form-label">Quick Date Range</label>
                                <select id="quick_range" class="form-control">
                                    <option value="">-- Select --</option>
                                    <option value="this_week">This Week</option>
                                    <option value="last_week">Last Week</option>
                                    <option value="this_month">This Month</option>
                                    <option value="last_month">Last Month</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                            </div>
                            <div class="col-auto">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                            </div>
                            <div class="col-auto">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select name="payment_method" id="payment_method" class="form-control">
                                    <option value="">All</option>
                                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="tng_wallet" {{ request('payment_method') == 'tng_wallet' ? 'selected' : '' }}>TNG Wallet</option>
                                </select>
                            </div>
                            <div class="col-auto d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Search</button>
                                <a href="{{ route('reports.profit') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                    <script>
                        document.getElementById('quick_range').addEventListener('change', function() {
                            const today = new Date();
                            let start, end;
                            const pad = n => n.toString().padStart(2, '0');
                            function format(date) {
                                return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
                            }
                            if (this.value === 'this_week') {
                                const day = today.getDay() || 7;
                                start = new Date(today);
                                start.setDate(today.getDate() - day + 1);
                                end = new Date(today);
                            } else if (this.value === 'last_week') {
                                const day = today.getDay() || 7;
                                end = new Date(today);
                                end.setDate(today.getDate() - day);
                                start = new Date(end);
                                start.setDate(end.getDate() - 6);
                            } else if (this.value === 'this_month') {
                                start = new Date(today.getFullYear(), today.getMonth(), 1);
                                end = new Date(today);
                            } else if (this.value === 'last_month') {
                                start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                                end = new Date(today.getFullYear(), today.getMonth(), 0);
                            } else {
                                return;
                            }
                            document.getElementById('start_date').value = format(start);
                            document.getElementById('end_date').value = format(end);
                        });
                    </script>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <canvas id="profitChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        @php
                            // Sort $profitData by date ascending for chart
                            $sortedProfitData = $profitData->sortBy('date')->values();
                        @endphp
                        const chartLabels = {!! json_encode($sortedProfitData->pluck('date')->map(function($d) use ($period) {
                            return $period === 'monthly' ? \Carbon\Carbon::createFromFormat('Y-m', $d)->format('M Y') : \Carbon\Carbon::parse($d)->format('Y-m-d');
                        })) !!};
                        const chartSales = {!! json_encode($sortedProfitData->pluck('sales')) !!};
                        const chartCogs = {!! json_encode($sortedProfitData->pluck('cogs')) !!};
                        const chartProfit = {!! json_encode($sortedProfitData->pluck('profit')) !!};
                        const ctx = document.getElementById('profitChart').getContext('2d');
                        const profitChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: chartLabels,
                                datasets: [
                                    {
                                        label: 'Sales (RM)',
                                        data: chartSales,
                                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                        borderColor: 'rgba(54, 162, 235, 1)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Cost (RM)',
                                        data: chartCogs,
                                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                                        borderColor: 'rgba(255, 99, 132, 1)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Profit (RM)',
                                        data: chartProfit,
                                        type: 'line',
                                        borderColor: 'rgba(40, 167, 69, 1)',
                                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                                        tension: 0.3,
                                        fill: false,
                                        yAxisID: 'y',
                                        pointRadius: 3,
                                        pointHoverRadius: 5
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { position: 'top' },
                                    title: { display: false }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Amount (RM)'
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Date'
                                        }
                                    }
                                }
                            }
                        });
                    </script>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Profit</h5>
                                    <h2 class="card-text">RM{{ number_format($totalProfit, 2) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Cost</h5>
                                    <h2 class="card-text">RM{{ number_format($totalCogs, 2) }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ $period === 'monthly' ? 'Month' : 'Date' }}</th>
                                    <th>Total Sales</th>
                                    <th>Total Cost</th>
                                    <th>Total Profit</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($profitData as $i => $data)
                                <tr>
                                    <td>
                                        @if($period === 'monthly')
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $data->date)->format('F Y') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($data->date)->format('Y-m-d') }}
                                        @endif
                                    </td>
                                    <td>RM{{ number_format($data->sales, 2) }}</td>
                                    <td>RM{{ number_format($data->cogs, 2) }}</td>
                                    <td>RM{{ number_format($data->profit, 2) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('details-{{ $i }}').classList.toggle('d-none')">
                                            Show Details
                                        </button>
                                    </td>
                                </tr>
                                <tr id="details-{{ $i }}" class="d-none">
                                    <td colspan="5">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Qty</th>
                                                    <th>Total Sales</th>
                                                    <th>Total Cost</th>
                                                    <th>Total Profit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data->products as $product)
                                                <tr>
                                                    <td>{{ $product->product_name }}</td>
                                                    <td>{{ $product->quantity }}</td>
                                                    <td>RM{{ number_format($product->sales, 2) }}</td>
                                                    <td>RM{{ number_format($product->cogs, 2) }}</td>
                                                    <td>RM{{ number_format($product->profit, 2) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        @if($profitData->lastPage() > 1)
                            {{ $profitData->links('pagination::bootstrap-5') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 