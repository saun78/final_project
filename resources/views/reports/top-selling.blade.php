@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top Selling Products</h3>
                </div>
                <div class="card-body">
                    <!-- Top Selling Charts -->
                    <div class="row mb-4">
                        <div class="col-lg-8 col-12 mb-3 mb-lg-0">
                            <h4 class="mb-3">Top Selling Products </h4>
                            <div style="width:100%; min-width:300px; height:250px;">
                                <canvas id="topSellingBarChart" height="250" style="min-height:250px; width:100%;"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-4 col-12">
                            <h4 class="mb-3">Products Category</h4>
                            <div class="d-flex justify-content-center">
                                <div id="chartdiv" style="width: 100%; min-width:300px; height: 250px;"></div>
                            </div>
                        </div>
                    </div>
                    @php
                        $categoryTotals = collect($productTotals)
                            ->groupBy(function($total) {
                                return $total['product']?->category?->name ?? 'Unknown Category';
                            })
                            ->map(function($group, $category) {
                                return [
                                    'category' => $category,
                                    'value' => $group->sum('total_sold')
                                ];
                            })
                            ->sortByDesc('value')
                            ->take(10)
                            ->values();
                    @endphp
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
                    <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
                    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
                    <script>
                        const topProducts = @json(collect($productTotals)->take(10)->values()->map(function($total) {
                            return [
                                'name' => $total['product']?->name ?? 'Unknown Product',
                                'total_sold' => $total['total_sold']
                            ];
                        }));
                        // Bar Chart
                        const ctxBar = document.getElementById('topSellingBarChart').getContext('2d');
                        // Define a color palette
                        const barColors = [
                            '#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f', '#edc949', '#af7aa1', '#ff9da7', '#9c755f', '#bab0ab',
                            '#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf'
                        ];
                        new Chart(ctxBar, {
                            type: 'bar',
                            data: {
                                labels: topProducts.map(p => p.name),
                                datasets: [{
                                    label: 'Quantity Sold',
                                    data: topProducts.map(p => p.total_sold),
                                    backgroundColor: topProducts.map((p, i) => barColors[i % barColors.length]),
                                    borderColor: topProducts.map((p, i) => barColors[i % barColors.length]),
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { display: false },
                                    title: { display: false }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { precision: 0 }
                                    }
                                }
                            }
                        });
                        // amCharts 5 Pie Chart
                        am5.ready(function() {
                            let root = am5.Root.new("chartdiv");
                            root.setThemes([
                                am5themes_Animated.new(root)
                            ]);
                            let chart = root.container.children.push(
                                am5percent.PieChart.new(root, {
                                    endAngle: 270
                                })
                            );
                            let series = chart.series.push(
                                am5percent.PieSeries.new(root, {
                                    valueField: "value",
                                    categoryField: "category",
                                    endAngle: 270
                                })
                            );
                            // Prepare category data from PHP
                            const am5Data = @json($categoryTotals);
                            series.data.setAll(am5Data);
                            series.states.create("hidden", {
                                endAngle: -90
                            });
                        });
                    </script>

                    <!-- Search Form -->
                    <form action="{{ route('reports.top-selling') }}" method="GET" class="mb-4">
                        <div class="row align-items-end g-2">
                            <div class="col-auto">
                                <label for="search" class="form-label">Search Products</label>
                                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name, part number, category, or brand">
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
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-auto">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('reports.top-selling') }}" class="btn btn-default w-100">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
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
                                @if($salesRecords->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center">—</td>
                                </tr>
                                @else
                                    @foreach($salesRecords as $record)
                                    <tr>
                                        <td>{{ $record->datetime }}</td>
                                        <td>{{ $record->product?->name ?: '—' }}</td>
                                        <td>{{ $record->product?->part_number ?: '—' }}</td>
                                        <td>{{ $record->category?->name ?? '—' }}</td>
                                        <td>{{ $record->brand?->name ?? '—' }}</td>
                                        <td>{{ $record->quantity !== null ? number_format($record->quantity) : '—' }}</td>
                                        <td>{{ ($record->amount !== null) ? 'RM' . number_format($record->amount, 2) : '—' }}</td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if($salesRecords->lastPage() > 1)
                        <div class="d-flex justify-content-center">
                            {{ $salesRecords->links('pagination::bootstrap-5') }}
                        </div>
                    @endif

                    <!-- Product Totals -->
                    <h4 class="mb-3">Product Totals Sold</h4>
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
                                    <td>{{ $total['product']?->name ?: '—' }}</td>
                                    <td>{{ $total['product']?->part_number ?: '—' }}</td>
                                    <td>{{ $total['product']?->category?->name ?? '—' }}</td>
                                    <td>{{ $total['product']?->brand?->name ?? '—' }}</td>
                                    <td>{{ isset($total['total_sold']) ? number_format($total['total_sold']) : '—' }}</td>
                                    <td>{{ isset($total['total_amount']) ? 'RM' . number_format($total['total_amount'], 2) : '—' }}</td>
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