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
                                <div class="card-body">
                                    <h5 class="card-title">Top Selling Products</h5>
                                    <p class="card-text">View the most sold products and their sales statistics.</p>
                                    <a href="{{ route('reports.top-selling') }}" class="btn btn-primary">
                                        <i class="fas fa-chart-line"></i> View Report
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Sales by Period</h5>
                                    <p class="card-text">Analyze sales data by daily or monthly periods.</p>
                                    <a href="{{ route('reports.sales-by-period') }}" class="btn btn-primary">
                                        <i class="fas fa-calendar-alt"></i> View Report
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