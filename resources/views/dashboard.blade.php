@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $currentMonth = \Carbon\Carbon::now()->format('F');
    $recentDays = [];
    $recentYmdDates = [];
    $recentDates = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = \Carbon\Carbon::now()->subDays($i);
        $recentDays[] = $date->format('d');
        $recentYmdDates[] = $date->format('Y-m-d');
        $recentDates[] = $date->format('M d');
    }
@endphp

@section('content')
@if (session('popup_message'))
    @php
        $popupMessage = session('popup_message');
        session()->forget('popup_message');
    @endphp

    <div id="login-success-popup" style="position: fixed; top: 30px; left: 50%; transform: translateX(-50%);
        background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 20px 30px;
        border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-size: 1.1rem; z-index: 9999;
        display: flex; align-items: center; gap: 10px; transition: opacity 0.5s ease;">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ $popupMessage }}</span>
        <button onclick="dismissPopup()" style="background: none; border: none;
                font-size: 1.2rem; margin-left: 10px; color: #155724;">&times;</button>
    </div>

    <script>
        function dismissPopup() {
            const popup = document.getElementById('login-success-popup');
            if (popup) {
                popup.style.opacity = '0';
                setTimeout(() => popup.remove(), 500);
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            const popup = document.getElementById('login-success-popup');
            if (popup) {
                setTimeout(() => {
                    popup.style.opacity = '0';
                    setTimeout(() => popup.remove(), 500);
                }, 3000);
            }
        });
    </script>
@endif


@push('styles')
<style>
/* 隐藏 ApexCharts 的 tooltip 小黑格 */
.apexcharts-xaxistooltip {
    display: none !important;
}
.apexcharts-tooltip-series-group:not(:first-child) {
    display: none !important;
}
.apexcharts-tooltip .apexcharts-tooltip-text-x-value,
.apexcharts-tooltip .apexcharts-tooltip-title {
    display: none !important;
}
.apexcharts-tooltip .apexcharts-tooltip-series-group[style*="position: absolute"] {
    display: none !important;
}
/* 鼠标悬停圆点时为 pointer */
#summaryMiniLineApex, 
#summaryMiniLineApex svg, 
#summaryMiniLineApex .apexcharts-series-markers,
#summaryMiniLineApex .apexcharts-marker {
    cursor: pointer !important;
}
/* 隐藏 ApexCharts 饼图中心小文字 */
.apexcharts-pie .apexcharts-datalabels-group text {
    display: none !important;
}
/* 让 tooltip 不会被父容器裁剪 */
.dashboard-card, .card-body, .chart-container {
    overflow: visible !important;
}
.apexcharts-tooltip {
    z-index: 9999 !important;
}
</style>
@endpush
    <!-- Dashboard Cards -->
<!-- Statistics Dashboard Cards -->
<div class="row g-4 mb-4">
    <!-- Top Selling -->
    <div class="col-md-3">
        <div class="card dashboard-card text-white" style="background: linear-gradient(135deg, #007bff 60%, #0056b3 100%); position:relative; min-height: 320px;">
            <div class="card-body">
                <div class="card-title">Top Selling</div>
                <div class="main-value text-white">{{ $currentMonth }}</div>
                <div class="my-2 chart-container"  style="height: 100px; margin-bottom: 1.5rem;">
                    <div id="topSellingMiniPie" style="height: 220px;"></div>
                </div>
                {{-- <div class="d-flex justify-content-between text-white-50 small mb-2">
                    @foreach($recentDays as $day)
                        <span class="dashboard-date-label">{{ $day }}</span>
                    @endforeach
                </div> --}}
                <svg class="icon" width="39" height="39" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg" style="position:absolute;right:1rem;top:2.8rem;opacity:0.8;">
                  <circle cx="19" cy="19" r="16" stroke="white" stroke-width="3" fill="none"/>
                  <path d="M19 19 L19 3 A16 16 0 0 1 35 19 Z" fill="white" fill-opacity="0.7"/>
                  <path d="M19 19 L35 19 A16 16 0 0 1 19 35 Z" fill="white" fill-opacity="0.4"/>
                </svg>
                <a href="{{ route('reports.top-selling') }}" class="btn btn-light btn-sm w-100" style="margin-top:2rem;">View Details</a>
            </div>
        </div>
    </div>
    <!-- Summary -->
    <div class="col-md-3">
        <div class="card dashboard-card text-white" style="background: linear-gradient(135deg, #28a745 60%, #218838 100%); position:relative; min-height: 320px;">
            <div class="card-body">
                <div class="card-title">Summary</div>
                <div class="main-value text-white">{{ $currentMonth }}</div>
                <div class="my-2 chart-container" style="height: 100px; margin-bottom: 1.5rem;">
                    <div id="summaryMiniLineApex" style="height: 100px; margin-top: 0.7rem;"></div>
                </div>
                <i class="bi bi-graph-up-arrow icon" style="position:absolute;right:1rem;top:2.8rem;opacity:0.8;"></i>
                <a href="{{ route('reports.summary') }}" class="btn btn-light btn-sm w-100" style="margin-top:1.7rem;">View Details</a>
            </div>
        </div>
    </div>
    <!-- Profit Report -->
    <div class="col-md-3">
        <div class="card dashboard-card text-white" style="background: linear-gradient(135deg, #ffc107 60%, #ff9800 100%); position:relative; min-height: 320px;">
            <div class="card-body">
                <div class="card-title">Profit Report</div>
                <div class="main-value text-white">{{ $currentMonth }}</div>
                <div class="my-2 chart-container" style="height: 100px; margin-bottom: 1.5rem;">
                    <div id="profitMiniBarApex" style="height: 100px; margin-top: 0.7rem;"></div>
                </div>
                {{-- <div class="d-flex justify-content-between text-white-50 small mb-2">
                    @foreach($recentDays as $day)
                        <span class="dashboard-date-label">{{ $day }}</span>
                    @endforeach
                </div> --}}
                <svg class="icon" width="38" height="38" viewBox="0 0 38 38" style="position:absolute;right:1rem;top:2.8rem;opacity:0.8;" xmlns="http://www.w3.org/2000/svg">
  <text x="50%" y="50%" text-anchor="middle" dominant-baseline="central" font-size="36" font-family="Arial, Helvetica, sans-serif" font-weight="400" fill="#fff">$</text>
</svg>
                <a href="{{ route('reports.profit') }}" class="btn btn-light btn-sm w-100" style="margin-top:1.6rem;">View Details</a>
            </div>
        </div>
    </div>
    <!-- Low Stock Alert -->
    <div class="col-md-3">
        <div class="card dashboard-card text-white" style="background: linear-gradient(135deg, #dc3545 60%, #b21f2d 100%); position:relative; min-height: 320px;">
            <div class="card-body">
                <div class="card-title">Low Stock Alert</div>
                <div class="main-value">{{ $lowStockProducts + $outOfStockProducts }}</div>
                <div class="d-flex align-items-center text-white-50 small mb-2">
                    <i class="bi bi-info-circle me-2"></i>
                    <span>{{ $lowStockProducts }} low, {{ $outOfStockProducts }} out</span>
                </div>
                <i class="bi bi-exclamation-triangle-fill icon" style="position:absolute;right:1rem;top:2.8rem;opacity:0.8;"></i>
                <a href="{{ route('products.index') }}?filter=low_stock" class="btn btn-light btn-sm w-100" style="margin-top:2.4rem;">View Products</a>
            </div>
        </div>
    </div>
</div>


    <!-- Recent Inventory Movements -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Recent Inventory Movements</h5>
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
                            <th>Supplier</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentMovements as $movement)
                            <tr>
                                <td>{{ $movement->movement_date->format('Y-m-d H:i') }}</td>
                                <td>{{ $movement->product->name ?? 'Unknown Product' }}</td>
                                <td>
                                    @if($movement->movement_type == 'stock_in')
                                        <span class="badge bg-success">In</span>
                                    @elseif($movement->movement_type == 'sale')
                                        <span class="badge bg-danger">Out</span>
                                    @elseif($movement->movement_type == 'stock_out')
                                        <span class="badge bg-warning">Out</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($movement->movement_type) }}</span>
                                    @endif
                                </td>
                                <td>{{ abs($movement->quantity) }}</td>
                                <td>{{ $movement->product->supplier->contact_person ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('inventory-movements.show', ['movement' => $movement->id, 'page' => request('page')]) }}"
                                        class="btn btn-sm btn-outline-primary" title="View Details">
                                         <i class="bi bi-eye"></i>
                                     </a>
                                     
                                </td>
                                
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    <i class="bi bi-inbox me-2"></i>
                                    No inventory movements today
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center my-3">
                {{ $recentMovements->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    @if($lowStockProducts > 0 || $outOfStockProducts > 0)
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <strong>Warning!</strong> {{ $lowStockProducts + $outOfStockProducts }} products are running low on stock. Please check the inventory.
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0"></script>
<script>
    const recentYmdDates = @json($recentYmdDates);
    const recentDates = @json($recentDates);
    const topSellingData = @json($topSellingData);
    const summaryData = @json($summaryData);
    const profitData = @json($profitData);

    // Top Selling Mini Pie Chart（极简测试版）
    const productTotals = @json($productTotals ?? []);
    const filteredProductTotals = productTotals.filter(item => item && item.total_sold > 0);
    const pieLabels = filteredProductTotals.map(item => item.name);
    const pieSeries = filteredProductTotals.map(item => item.total_sold);

    

    const pieOptions = {
        chart: { type: 'pie', height:150, toolbar: { show: false } },
        labels: pieLabels,
        series: pieSeries,
        legend: { show: false },
        tooltip: { enabled: true, theme: 'dark' },
        colors: ['#FF8360', '#FFD460', '#6EE7B7', '#B39DDB', '#F48FB1'],
        plotOptions: {
            pie: {
                expandOnClick: false
            }
        },
        states: {
            active: { filter: { type: 'none', value: 0 } },
            hover: { filter: { type: 'lighten', value: 0.15 } }
        }
    };

    if (pieSeries.length > 0 && pieSeries.some(v => v > 0)) {
        var pieChart = new ApexCharts(document.querySelector("#topSellingMiniPie"), pieOptions);
        pieChart.render();
    } else {
        document.querySelector("#topSellingMiniPie").innerHTML = "<div style='color:#fff;text-align:center;padding-top:30px;'>No data</div>";
    }

// 假设 recentDates 是像 "2025-07-11" 这样格式的日期数组
let summaryLabels = recentDates.map(date => {
    let d = new Date(date);
    return d.getDate().toString(); // 只要 "11", "12", ...
});

let summaryAmounts = recentYmdDates.map(date =>
    summaryData[date] ? Number(summaryData[date].total_amount) : 0
);
console.log('summaryLabels', summaryLabels);
console.log('summaryAmounts', summaryAmounts);

var summaryOptions = {
    chart: {
        type: 'line',
        height: 100,
        toolbar: { show: false },
        animations: { enabled: true },
        events: {
            mouseMove: function (event, chartContext, config) {
                chartContext.el.style.cursor = 'pointer';
            }
        }
    },
    series: [{
        data: summaryAmounts
    }],
    xaxis: {
        categories: summaryLabels,
        labels: {
            show: true,
            style: {
                colors: '#fff',
                fontSize: '13px'
            },
            offsetY: 0
        }
    },
    yaxis: {
        show: false,
        min: 0,
        max: Math.max(...summaryAmounts) + 100
    },
    stroke: {
        show: true,
        curve: 'smooth',
        width: 3,
        colors: ['#fff'],
        dashArray: 0
    },
    markers: {
        size: 4,
        shape: 'circle',
        colors: ['#fff'],
        strokeColors: '#fff',
        strokeWidth: 2
    },
    tooltip: {
        enabled: true,
        theme: 'dark',
        shared: false,
        x: { show: false },
        custom: function({ series, seriesIndex, dataPointIndex, w }) {
            const day = summaryLabels[dataPointIndex];
            const amount = series[seriesIndex][dataPointIndex];
            return `<div style='padding:6px 12px; font-size:15px;'>
                <div style='font-weight:bold;'>${day}</div>
                <div>Total Amount: ${amount}</div>
            </div>`;
        }
    },
    colors: ['#fff'],
    grid: { show: false }
};

var summaryChart = new ApexCharts(document.querySelector("#summaryMiniLineApex"), summaryOptions);
summaryChart.render();


    // Mini Bar Chart for Profit Report (ApexCharts)
    let profitLabels = recentYmdDates.map(date => {
        const d = new Date(date);
        return d.getDate();
    });
    let profitAmounts = recentYmdDates.map(date => profitData[date] ? profitData[date].total_profit : 0);

    var options = {
        chart: {
            type: 'bar',
            height: 100, // 与 summary 保持一致
            toolbar: { show: false }
        },
        series: [{
            data: profitAmounts
        }],
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: profitLabels,
            labels: {
                show: true,
                style: {
                    colors: '#fff',
                    fontSize: '13px',
                    fontWeight: 400,
                    opacity: 0.7
                },
                rotate: -20,
                offsetY: 0
            },
            axisTicks: { show: false },
            axisBorder: {
                show: true,
                color: '#fff',
                height: 1,
                offsetY: 0
            }
        },
        yaxis: {
            show: false,
            min: 0,
            max: 120 // 让柱子更高一点点
        },
        plotOptions: {
            bar: {
                columnWidth: '60%',
                borderRadius: 4
            }
        },
        states: {
            normal: { filter: { type: 'none', value: 0 } },
            active: { filter: { type: 'none', value: 0 } },
            hover:  { filter: { type: 'none', value: 0 } }
        },
        grid: {
            show: false,
            padding: {
                bottom: 0,
                top: 0,
                left: 0,
                right: 0
            }
        },
        tooltip: {
            enabled: true,
            theme: 'dark',
            custom: function({series, seriesIndex, dataPointIndex, w}) {
                const d = new Date(recentYmdDates[dataPointIndex]);
                const day = d.getDate();
                const profit = series[seriesIndex][dataPointIndex];
                return `<div style='padding:6px 12px; font-size:15px;'>
                    <div style='font-weight:bold;'>${day}</div>
                    <div>Total Profit: ${profit}</div>
                </div>`;
            },
            style: {
                fontSize: '15px'
            },
            fixed: {
                enabled: false
            }
        },
        colors: ['#fff']
    };

    var chart = new ApexCharts(document.querySelector("#profitMiniBarApex"), options);
    chart.render();
</script>
@endpush 

<style>
/* 彻底隐藏 ApexCharts x 轴 tooltip 小黑格 */
.apexcharts-xaxistooltip {
    display: none !important;
}
/* 隐藏所有 ApexCharts tooltip 右下角小黑格（包括所有 series group） */
.apexcharts-tooltip-series-group:not(:first-child) {
    display: none !important;
}
/* 兜底：隐藏所有 tooltip 里的只有数字的 div */
.image.pngapexcharts-tooltip .apexcharts-tooltip-text-x-value,
.apexcharts-tooltip .apexcharts-tooltip-title {
    display: none !important;
}
/* 隐藏所有右下角绝对定位的小黑格 */
.apexcharts-tooltip .apexcharts-tooltip-series-group[style*="position: absolute"] {
    display: none !important;
}
/* 鼠标悬停圆点时为 pointer */
#summaryMiniLineApex, 
#summaryMiniLineApex svg, 
#summaryMiniLineApex .apexcharts-series-markers,
#summaryMiniLineApex .apexcharts-marker {
    cursor: pointer !important;
}
/* 隐藏 ApexCharts 饼图中心的小圆点/marker */
.apexcharts-pie .apexcharts-datalabels-group text {
    display: none !important;
}
/* 保证 ApexCharts tooltip 不被裁剪 */
.dashboard-card, .card-body, .chart-container {
    overflow: visible !important;
}
.apexcharts-tooltip {
    z-index: 9999 !important;
}
</style> 