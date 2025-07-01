@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sales by Period</h3>
                    <div class="card-tools">
                        <a href="{{ route('reports.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Back to Reports
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.sales-by-period') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="period">Report Period</label>
                                    <select name="period" id="period" class="form-control">
                                        <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">Generate Report</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Items Sold</h5>
                                    <h2 class="card-text">{{ number_format($totalSales) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Sales Amount</h5>
                                    <h2 class="card-text">RM{{ number_format($totalAmount, 2) }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Total Sold</th>
                                    <th>Payment Method</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesData as $data)
                                <tr>
                                    <td>{{ $period === 'monthly' ? Carbon\Carbon::parse($data->date)->format('F Y') : Carbon\Carbon::parse($data->date)->format('Y-m-d') }}</td>
                                    <td>{{ number_format($data->total_sold) }}</td>
                                    <td>{{ number_format($data->payment_method) }}</td>
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