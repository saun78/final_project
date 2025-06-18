@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Reports</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h4 class="card-title">Top Selling Products</h4>
                                    <p class="card-text">View your best-selling products and their performance metrics.</p>
                                    <a href="{{ route('reports.top-selling') }}" class="btn btn-primary">
                                        <i class="fas fa-chart-line"></i> View Top Selling
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h4 class="card-title">Sales Report</h4>
                                    <p class="card-text">Analyze your sales data by daily or monthly periods.</p>
                                    <a href="{{ route('reports.sales-by-period') }}" class="btn btn-success">
                                        <i class="fas fa-calendar-alt"></i> View Sales Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 