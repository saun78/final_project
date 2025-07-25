@extends('layouts.app')

@section('content')
<style>
  .card.border-dark, .card-header.border-bottom.border-dark {
    border-radius: 0 !important;
    border-color: #000 !important;
    box-shadow: none !important;
  }
  .card-header.bg-light.border-bottom.border-dark {
    border-top-left-radius: 0 !important;
    border-top-right-radius: 0 !important;
  }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Summary</h3>
                    <div class="card-tools">
                    </div>
                </div>
                <!-- Chart.js Line Charts Grid -->
                <div class="row p-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="card h-100 border border-dark">
                            <div class="card-header bg-light border-bottom border-dark">
                                <h5 class="mb-0">Total Sales</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="summaryLineChart" style="min-height:250px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border border-dark">
                            <div class="card-header bg-light border-bottom border-dark">
                                <h5 class="mb-0">Total Amount</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="cumulativeSalesChart" style="min-height:250px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row px-4 pb-4">
                    <div class="col-12">
                        <div class="card h-100 border border-dark">
                            <div class="card-header bg-light border-bottom border-dark">
                                <h5 class="mb-0">Sales by Payment Method</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="paymentMethodLineChart" style="min-height:300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    // First chart: daily/monthly totals
                    const ctx = document.getElementById('summaryLineChart').getContext('2d');
                    const summaryLineChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($chartData->pluck('date')->values()),
                            datasets: [{
                                label: 'Total Sales',
                                data: @json($chartData->pluck('total_amount')->values()),
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                fill: true,
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { title: { display: true, text: 'Date' } },
                                y: { title: { display: true, text: 'Total Sales' } }
                            }
                        }
                    });

                    // Second chart: cumulative total sales
                    const totals = @json($chartData->pluck('total_amount')->values());
                    const cumulativeTotals = totals.reduce((acc, val, i) => {
                        acc.push((acc.length ? acc[acc.length-1] : 0) + parseFloat(val));
                        return acc;
                    }, []);
                    const ctx2 = document.getElementById('cumulativeSalesChart').getContext('2d');
                    const cumulativeSalesChart = new Chart(ctx2, {
                        type: 'line',
                        data: {
                            labels: @json($chartData->pluck('date')->values()),
                            datasets: [{
                                label: 'Cumulative Total Sales',
                                data: cumulativeTotals,
                                borderColor: 'rgba(255, 99, 132, 1)',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                fill: true,
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { title: { display: true, text: 'Date' } },
                                y: { title: { display: true, text: 'Cumulative Sales' } }
                            }
                        }
                    });

                    // Third chart: sales by payment method
                    const cashAmounts = @json($chartData->pluck('cash_amount')->values() ?? []);
                    const tngAmounts = @json($chartData->pluck('tng_amount')->values() ?? []);
                    const cardAmounts = @json($chartData->pluck('card_amount')->values() ?? []);
                    const ctx3 = document.getElementById('paymentMethodLineChart').getContext('2d');
                    const paymentMethodLineChart = new Chart(ctx3, {
                        type: 'line',
                        data: {
                            labels: @json($chartData->pluck('date')->values()),
                            datasets: [
                                {
                                    label: 'Cash',
                                    data: cashAmounts,
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                                    fill: false,
                                    tension: 0.1
                                },
                                {
                                    label: 'TNG',
                                    data: tngAmounts,
                                    borderColor: 'rgba(255, 206, 86, 1)',
                                    backgroundColor: 'rgba(255, 206, 86, 0.1)',
                                    fill: false,
                                    tension: 0.1
                                },
                                {
                                    label: 'Card',
                                    data: cardAmounts,
                                    borderColor: 'rgba(153, 102, 255, 1)',
                                    backgroundColor: 'rgba(153, 102, 255, 0.1)',
                                    fill: false,
                                    tension: 0.1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { title: { display: true, text: 'Date' } },
                                y: { title: { display: true, text: 'Sales Amount' } }
                            }
                        }
                    });
                </script>
                <div class="card-body">
                    <form action="{{ route('reports.summary') }}" method="GET" class="mb-4">
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
                                    <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="tng_wallet" {{ request('payment_method') == 'tng_wallet' ? 'selected' : '' }}>TNG Wallet</option>
                                </select>
                            </div>
                            <div class="col-auto d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Search</button>
                                <a href="{{ route('reports.summary') }}" class="btn btn-secondary">Reset</a>
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
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Sales Amount</h5>
                                    <h2 class="card-text">RM{{ number_format($totalAmount, 2) }}</h2>
                                    <div class="mt-2">
                                        <span class="fw-bold">Total Receipts:</span>
                                        {{ $salesData->sum(fn($d) => $d->receipts->count()) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Cash Total</h5>
                                    <h3 class="card-text">RM{{ number_format($cashTotal, 2) }}</h3>
                                    <div class="mt-2">
                                        <span class="fw-bold">Total Receipts:</span>
                                        {{ $salesData->sum(fn($d) => $d->receipts->where('payment_method', 'cash')->count()) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">TNG Total</h5>
                                    <h3 class="card-text">RM{{ number_format($tngTotal, 2) }}</h3>
                                    <div class="mt-2">
                                        <span class="fw-bold">Total Receipts:</span>
                                        {{ $salesData->sum(fn($d) => $d->receipts->where('payment_method', 'tng_wallet')->count()) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Bank Transfer Total</h5>
                                    <h3 class="card-text">RM{{ number_format($cardTotal, 2) }}</h3>
                                    <div class="mt-2">
                                        <span class="fw-bold">Total Receipts:</span>
                                        {{ $salesData->sum(fn($d) => $d->receipts->where('payment_method', 'Bank Transfer')->count()) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ $period === 'monthly' ? 'Month' : 'Date' }}</th>
                                    <th>Total Orders</th>
                                    <th>Cash Amount</th>
                                    <th>TNG Amount</th>
                                    <th>Bank Transfer Amount</th>
                                    <th>Total Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesData as $index => $data)
                                <tr>
                                    <td>
                                        @if($period === 'monthly')
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $data->date)->format('F Y') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($data->date)->format('Y-m-d') }}
                                        @endif
                                    </td>
                                    <td>{{ $data->receipts->count() }}</td>
                                    <td>RM{{ number_format($data->cash_amount, 2) }}</td>
                                    <td>RM{{ number_format($data->tng_amount, 2) }}</td>
                                    <td>RM{{ number_format($data->bank_transfer_amount, 2) }}</td>
                                    <td>RM{{ number_format($data->total_amount, 2) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" type="button" data-bs-toggle="collapse" data-bs-target="#receipts-{{ $index }}" aria-expanded="false" aria-controls="receipts-{{ $index }}">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                                <tr class="collapse-row">
                                    <td colspan="6" style="padding:0; border:none;">
                                        <div class="collapse" id="receipts-{{ $index }}">
                                            <div class="card card-body mb-2">
                                                <h6>Receipts for this {{ $period === 'monthly' ? 'month' : 'day' }}:</h6>
                                                @if($data->receipts->count())
                                                    <table class="table table-sm table-bordered mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Time</th>
                                                                <th>Receipt Number</th>
                                                                <th>Payment Method</th>
                                                                <th>Total Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($data->receipts as $receipt)
                                                                <tr style="cursor:pointer" onclick="window.open('/orders/{{ $receipt['id'] }}', '_blank')">
                                                                    <td>{{ $receipt['time'] }}</td>
                                                                    <td>{{ $receipt['order_number'] }}</td>
                                                                    <td>{{ ucfirst(str_replace('_', ' ', $receipt['payment_method'])) }}</td>
                                                                    <td>RM{{ number_format($receipt['total_amount'], 2) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @else
                                                    <div class="text-muted">No receipts found for this {{ $period === 'monthly' ? 'month' : 'day' }}.</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        @if($salesData->lastPage() > 1)
                            {{ $salesData->links('pagination::bootstrap-5') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 