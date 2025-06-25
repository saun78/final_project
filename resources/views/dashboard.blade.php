@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Dashboard Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Products</h6>
                            <h2 class="mt-2 mb-0">1,234</h2>
                        </div>
                        <div class="icon">
                            <i cla1ss="bi bi-box-seam"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Stock</h6>
                            <h2 class="mt-2 mb-0">45,678</h2>
                        </div>
                        <div class="icon">
                            <i class="bi bi-archive"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Low Stock</h6>
                            <h2 class="mt-2 mb-0">15</h2>
                        </div>
                        <div class="icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Today's Orders</h6>
                            <h2 class="mt-2 mb-0">28</h2>
                        </div>
                        <div class="icon">
                            <i class="bi bi-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Inventory Movements -->
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Inventory Movements</h5>
            <button class="btn btn-primary btn-sm">
                <i class="bi bi-plus"></i> New Movement
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Operator</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2024-06-11 10:00</td>
                            <td>Product A</td>
                            <td><span class="badge bg-success">In</span></td>
                            <td>100</td>
                            <td>John Doe</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2024-06-11 09:30</td>
                            <td>Product B</td>
                            <td><span class="badge bg-danger">Out</span></td>
                            <td>20</td>
                            <td>Jane Smith</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2024-06-10 16:20</td>
                            <td>Product C</td>
                            <td><span class="badge bg-success">In</span></td>
                            <td>50</td>
                            <td>Mike Johnson</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="alert alert-warning d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div>
            <strong>Warning!</strong> 15 products are running low on stock. Please check the inventory.
        </div>
    </div>
@endsection 