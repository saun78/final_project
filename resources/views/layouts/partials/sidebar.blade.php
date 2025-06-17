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
                <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    Receipts
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('manage.index') }}" class="nav-link {{ request()->routeIs('manage.*') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i>
                    Add
                </a>
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
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-graph-up"></i>
                    Reports
                </a>
            </li>
        </ul>
    </div>
</nav> 