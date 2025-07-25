<nav class="topbar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
        <button class="btn btn-link text-dark me-3" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
        </button>
        <span class="topbar-brand">IMS</span>
    </div>
    <div class="d-flex align-items-center">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://ui-avatars.com/api/?name=Admin&background=3498db&color=fff" alt="" width="32" height="32" class="rounded-circle me-2">
                <span class="d-none d-md-inline">Admin</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown"> 
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