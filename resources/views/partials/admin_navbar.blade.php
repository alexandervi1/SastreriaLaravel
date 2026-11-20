<nav class="navbar navbar-dark sticky-top admin-topbar px-3 shadow">
    <a class="navbar-brand d-flex align-items-center col-lg-2 me-0" href="{{ route('admin.dashboard') }}">
        <img src="{{ asset($negocio['logo_tinta']) }}" alt="Logo {{ $negocio['nombre'] }}" class="me-2">
        <span class="fs-5 fw-bold text-white">Panel Control</span>
    </a>
    <div class="navbar-nav ms-auto d-none d-md-block">
        <div class="nav-item text-nowrap d-flex align-items-center gap-2">
            <span class="text-light me-3 fw-bold d-none d-lg-inline">
                <i class="fas fa-user-circle me-1"></i> Hola, {{ auth()->user()->nombre_completo ?? 'Admin' }}
            </span>
            <a class="btn btn-brand-soft btn-sm fw-bold px-3 d-flex align-items-center rounded-pill shadow-sm admin-topbar-btn" href="{{ route('home') }}" target="_blank">
                <i class="fas fa-external-link-alt me-2"></i> Tienda
            </a>
        </div>
    </div>
    <button class="navbar-toggler d-lg-none collapsed ms-auto" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
</nav>
