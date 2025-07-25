@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Summary</h3>
                    <div class="card-tools">
                    </div>
                </div>
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
                                <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank</option>
                                <option value="tng_wallet" {{ request('payment_method') == 'tng_wallet' ? 'selected' : '' }}>TNG Wallet</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
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
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Sales Amount</h5>
                                    <h2 class="card-text">RM{{ number_format($totalAmount, 2) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Cash Total</h5>
                                    <h3 class="card-text">RM{{ number_format($cashTotal, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">TNG Total</h5>
                                    <h3 class="card-text">RM{{ number_format($tngTotal, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ $period === 'monthly' ? 'Month' : 'Date' }}</th>
                                    <th>Cash Amount</th>
                                    <th>TNG Amount</th>
                                    <th>Card Amount</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesData as $data)
                                <tr>
                                    <td>
                                        @if($period === 'monthly')
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $data->date)->format('F Y') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($data->date)->format('Y-m-d') }}
                                        @endif
                                    </td>
                                    <td>RM{{ number_format($data->cash_amount, 2) }}</td>
                                    <td>RM{{ number_format($data->tng_amount, 2) }}</td>
                                    <td>RM{{ number_format($data->card_amount, 2) }}</td>
                                    <td>RM{{ number_format($data->total_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 