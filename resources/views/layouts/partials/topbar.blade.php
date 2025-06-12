<nav class="topbar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
        <button class="btn btn-link text-dark me-3" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
        </button>
        <span class="topbar-brand">IMS</span>
    </div>
    <div class="d-flex align-items-center">
        <div class="dropdown me-3">
            <button class="btn btn-link text-dark position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    3
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                <li><a class="dropdown-item" href="#">Low stock alert: Product A</a></li>
                <li><a class="dropdown-item" href="#">New order received</a></li>
                <li><a class="dropdown-item" href="#">Inventory update required</a></li>
            </ul>
        </div>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://ui-avatars.com/api/?name=Admin&background=3498db&color=fff" alt="" width="32" height="32" class="rounded-circle me-2">
                <span class="d-none d-md-inline">Admin</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Sign out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav> 