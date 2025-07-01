@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top Selling Products</h3>
                    <div class="card-tools">
                        <a href="{{ route('reports.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Back to Reports
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <form action="{{ route('reports.top-selling') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="search">Search Products</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" placeholder="Search by name, part number, category, or brand">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="compare_period">Compare Period</label>
                                    <select class="form-control" id="compare_period" name="compare_period">
                                        <option value="">Select Period</option>
                                        <option value="last_month" {{ request('compare_period') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                                        <option value="last_3_months" {{ request('compare_period') == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                                        <option value="last_6_months" {{ request('compare_period') == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                                        <option value="last_year" {{ request('compare_period') == 'last_year' ? 'selected' : '' }}>Last Year</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="{{ route('reports.top-selling') }}" class="btn btn-default">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Individual Sales Records -->
                    <h4 class="mb-3">Sales Records</h4>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Product Name</th>
                                    <th>Part Number</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Quantity Sold</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesRecords as $record)
                                <tr>
                                    <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>{{ $record->product->name }}</td>
                                    <td>{{ $record->product->part_number }}</td>
                                    <td>{{ $record->product->category->name }}</td>
                                    <td>{{ $record->product->brand->name }}</td>
                                    <td>{{ number_format($record->quantity_sold) }}</td>
                                    <td>RM{{ number_format($record->total_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Product Totals -->
                    <h4 class="mb-3">Product Totals</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Part Number</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Total Sold</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productTotals as $total)
                                <tr>
                                    <td>{{ $total['product']->name }}</td>
                                    <td>{{ $total['product']->part_number }}</td>
                                    <td>{{ $total['product']->category->name }}</td>
                                    <td>{{ $total['product']->brand->name }}</td>
                                    <td>{{ number_format($total['total_sold']) }}</td>
                                    <td>RM{{ number_format($total['total_amount'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td colspan="4" class="text-right">Grand Total:</td>
                                    <td></td>
                                    <td>RM{{ number_format($totalAmount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 