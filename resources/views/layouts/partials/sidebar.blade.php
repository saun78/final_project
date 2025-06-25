<nav class="sidebar">
    <div class="p-3">
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="bi bi-box"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('manage.index') }}" class="nav-link {{ request()->routeIs('manage.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i>
                    Manage
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    Receipts
                </a>
            </li>
            <li class="nav-item">
                <a href="#reportsSubmenu" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" data-bs-toggle="collapse">
                    <i class="bi bi-graph-up"></i>
                    Reports
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="reportsSubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="{{ route('reports.top-selling') }}" class="nav-link {{ request()->routeIs('reports.top-selling') ? 'active' : '' }}">
                                <i class="bi bi-bar-chart"></i>
                                Top Selling
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.sales-by-period') }}" class="nav-link {{ request()->routeIs('reports.sales-by-period') ? 'active' : '' }}">
                                <i class="bi bi-calendar-check"></i>
                                Sales by Period
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-arrow-left-right"></i>
                    Inventory
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-truck"></i>
                    Suppliers
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-people"></i>
                    Customers
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.sidebar .nav-link[data-bs-toggle="collapse"] {
    position: relative;
}

.sidebar .nav-link[data-bs-toggle="collapse"] .bi-chevron-down {
    transition: transform 0.2s;
}

.sidebar .nav-link[data-bs-toggle="collapse"][aria-expanded="true"] .bi-chevron-down {
    transform: rotate(180deg);
}

.sidebar .collapse .nav-link {
    padding-left: 2.5rem;
    font-size: 0.9rem;
}

.sidebar .collapse .nav-link i {
    font-size: 0.8rem;
}
</style> 