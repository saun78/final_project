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
<<<<<<< HEAD
                <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    Receipts
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs(['categories.*', 'brands.*', 'suppliers.*']) ? 'active' : '' }}" 
                   onclick="toggleSubmenu(event)" id="addNavLink">
                    <i class="bi bi-plus-circle"></i>
                    Add
                    <i class="bi bi-chevron-down ms-auto" id="addChevron" style="transform: {{ request()->routeIs(['categories.*', 'brands.*', 'suppliers.*']) ? 'rotate(180deg)' : 'rotate(0deg)' }};"></i>
                </a>
                <ul class="nav nav-pills flex-column ms-3" id="addSubmenu" style="display: {{ request()->routeIs(['categories.*', 'brands.*', 'suppliers.*']) ? 'block' : 'none' }};">
                    <li class="nav-item">
                        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                            <i class="bi bi-grid"></i>
                            Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('brands.index') }}" class="nav-link {{ request()->routeIs('brands.*') ? 'active' : '' }}">
                            <i class="bi bi-tags"></i>
                            Brands
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                            <i class="bi bi-truck"></i>
                            Suppliers
                        </a>
                    </li>
                </ul>
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
                        <a href="{{ route('reports.summary') }}" class="nav-link {{ request()->routeIs('reports.summary') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check"></i>
                            Summary
                       </a>
                    </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.profit') }}" class="nav-link {{ request()->routeIs('reports.profit') ? 'active' : '' }}">
                                <i class="bi bi-currency-dollar"></i>
                                Profit Report
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</nav>

<style>
#addSubmenu {
    transition: all 0.3s ease;
}

#addChevron {
    transition: transform 0.3s ease;
}

#addSubmenu .nav-link {
    font-size: 0.9em;
    padding: 0.5rem 1rem;
}

#addSubmenu .nav-link:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}
</style>

<script>
function toggleSubmenu(event) {
    event.preventDefault();
    const submenu = document.getElementById('addSubmenu');
    const chevron = document.getElementById('addChevron');
    
    if (submenu.style.display === 'none' || submenu.style.display === '') {
        submenu.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)';
    } else {
        submenu.style.display = 'none';
        chevron.style.transform = 'rotate(0deg)';
    }
}
</script> 
=======
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
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
