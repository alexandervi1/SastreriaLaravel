<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') · {{ $negocio['nombre'] }}</title>
    <link rel="icon" href="{{ asset($negocio['logo']) }}" type="image/svg+xml">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-body-tertiary">

    @include('partials.admin_navbar')

    <!-- Alertas de Seguridad Globales -->
    @if(auth()->check())
        @if(auth()->user()->password_changed_at === null)
            <div class="alert alert-danger rounded-0 border-0 m-0 text-center shadow-sm" style="z-index: 1050; position: relative;" role="alert">
                <i class="fas fa-exclamation-triangle fa-fade fa-lg me-2"></i>
                <strong>¡ALERTA CRÍTICA DE SEGURIDAD!</strong> Esta cuenta conserva su contraseña inicial. Cualquiera que conozca el sistema podría vulnerar su tienda. <a href="{{ route('admin.configuracion.edit') }}#seguridad" class="alert-link text-decoration-underline">Cambie su contraseña inmediatamente aquí</a>.
            </div>
        @elseif(auth()->user()->passwordVencida())
            <div class="alert alert-warning rounded-0 border-0 m-0 text-center shadow-sm text-dark" style="z-index: 1050; position: relative;" role="alert">
                <i class="fas fa-history fa-lg me-2"></i>
                <strong>Aviso de Seguridad:</strong> Su contraseña actual tiene {{ $passwordRotationDays }} días o más de antigüedad. Por políticas de seguridad, <a href="{{ route('admin.configuracion.edit') }}#seguridad" class="alert-link text-dark text-decoration-underline">le sugerimos actualizarla aquí</a>.
            </div>
        @endif
    @endif

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Offcanvas para móviles y columna en PC -->
            <div class="sidebar bg-white offcanvas-lg offcanvas-start vh-100 col-lg-2 p-0" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
                <div class="offcanvas-header border-bottom d-lg-none">
                    <h5 class="offcanvas-title fw-bold" id="sidebarMenuLabel"><i class="fas fa-bars me-2 text-primary"></i> Menú</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body d-lg-flex flex-column p-0 pt-lg-3 overflow-y-auto">
                    <ul class="nav flex-column gap-1 px-3 w-100">
                        <li class="nav-item">
                            <a class="nav-link rounded {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-dark' }}" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-home fa-fw me-2"></i> Dashboard
                            </a>
                        </li>

                        <li class="nav-section">Catálogo</li>
                        <li class="nav-item">
                            <a class="nav-link rounded d-flex align-items-center justify-content-between {{ request()->routeIs('admin.productos.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.productos.index') }}">
                                <span><i class="fas fa-shirt fa-fw me-2"></i> Productos</span>
                                @if($lowStockCount > 0)
                                    <span class="badge badge-menu" title="{{ $lowStockCount }} {{ $lowStockCount === 1 ? 'producto bajo el umbral' : 'productos bajo el umbral' }}">{{ $lowStockCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded {{ request()->routeIs('admin.categorias.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.categorias.index') }}">
                                <i class="fas fa-tags fa-fw me-2"></i> Categorías
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded {{ request()->routeIs('admin.telas.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.telas.index') }}">
                                <i class="fas fa-scroll fa-fw me-2"></i> Telas
                            </a>
                        </li>

                        <li class="nav-section">Ventas</li>
                        <li class="nav-item">
                            <a class="nav-link rounded d-flex align-items-center justify-content-between {{ request()->routeIs('admin.pedidos.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.pedidos.index') }}">
                                <span><i class="fas fa-box-open fa-fw me-2"></i> Pedidos</span>
                                @if($pendingOrders > 0)
                                    <span class="badge badge-menu" title="{{ $pendingOrders }} {{ $pendingOrders === 1 ? 'pedido pendiente' : 'pedidos pendientes' }}">{{ $pendingOrders }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded {{ request()->routeIs('admin.clientes.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.clientes.index') }}">
                                <i class="fas fa-users fa-fw me-2"></i> Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded d-flex align-items-center justify-content-between {{ request()->routeIs('admin.citas.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.citas.index') }}">
                                <span><i class="fas fa-calendar-check fa-fw me-2"></i> Agenda</span>
                                @if($citasHoy > 0)
                                    <span class="badge badge-menu" title="{{ $citasHoy }} {{ $citasHoy === 1 ? 'cita hoy' : 'citas hoy' }}">{{ $citasHoy }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded {{ request()->routeIs('admin.carritos.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.carritos.index') }}">
                                <i class="fas fa-cart-shopping fa-fw me-2"></i> Carritos
                            </a>
                        </li>

                        <li class="nav-section">Análisis</li>
                        <li class="nav-item">
                            <button class="nav-link rounded dropdown-toggle d-flex align-items-center justify-content-between w-100 border-0 {{ request()->routeIs('admin.reportes.*') ? 'active' : 'text-dark' }}" data-bs-toggle="collapse" data-bs-target="#reportesMenu" aria-expanded="{{ request()->routeIs('admin.reportes.*') ? 'true' : 'false' }}" aria-controls="reportesMenu">
                                <span><i class="fas fa-chart-line fa-fw me-2"></i> Reportes</span>
                            </button>
                            <div class="collapse overflow-hidden {{ request()->routeIs('admin.reportes.*') ? 'show' : '' }}" id="reportesMenu">
                                <ul class="nav flex-column ms-3 mt-1 pb-2">
                                    <li class="nav-item"><a class="nav-link rounded py-1 {{ request()->routeIs('admin.reportes.ventas') ? 'active' : 'text-secondary' }}" href="{{ route('admin.reportes.ventas') }}"><i class="fas fa-angle-right me-2"></i>Ventas Totales</a></li>
                                    <li class="nav-item"><a class="nav-link rounded py-1 {{ request()->routeIs('admin.reportes.mas-vendidos') ? 'active' : 'text-secondary' }}" href="{{ route('admin.reportes.mas-vendidos') }}"><i class="fas fa-angle-right me-2"></i>Más Vendidos</a></li>
                                    <li class="nav-item"><a class="nav-link rounded py-1 {{ request()->routeIs('admin.reportes.bajo-stock') ? 'active' : 'text-secondary' }}" href="{{ route('admin.reportes.bajo-stock') }}"><i class="fas fa-angle-right me-2"></i>Alerta Bajo Stock</a></li>
                                    <li class="nav-item"><a class="nav-link rounded py-1 {{ request()->routeIs('admin.reportes.stock-categoria') ? 'active' : 'text-secondary' }}" href="{{ route('admin.reportes.stock-categoria') }}"><i class="fas fa-angle-right me-2"></i>Stock Categoría</a></li>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-section">Sistema</li>
                        <li class="nav-item">
                            <button class="nav-link rounded dropdown-toggle d-flex align-items-center justify-content-between w-100 border-0 {{ request()->routeIs('admin.configuracion.*') ? 'active' : 'text-dark' }}" data-bs-toggle="collapse" data-bs-target="#configMenu" aria-expanded="{{ request()->routeIs('admin.configuracion.*') ? 'true' : 'false' }}" aria-controls="configMenu">
                                <span><i class="fas fa-cog fa-fw me-2"></i> Configuración</span>
                            </button>
                            <div class="collapse overflow-hidden {{ request()->routeIs('admin.configuracion.*') ? 'show' : '' }}" id="configMenu">
                                <ul class="nav flex-column ms-3 mt-1 pb-2">
                                    <li class="nav-item"><a class="nav-link rounded py-1 {{ request()->routeIs('admin.configuracion.edit') && request('tab') !== 'seguridad' ? 'active' : 'text-secondary' }}" href="{{ route('admin.configuracion.edit') }}"><i class="fas fa-angle-right me-2"></i>Datos del negocio</a></li>
                                    <li class="nav-item"><a class="nav-link rounded py-1 {{ request('tab') === 'seguridad' ? 'active' : 'text-secondary' }}" href="{{ route('admin.configuracion.edit', ['tab' => 'seguridad']) }}"><i class="fas fa-angle-right me-2"></i>Seguridad y acceso</a></li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rounded {{ request()->routeIs('admin.administradores.*') ? 'active' : 'text-dark' }}" href="{{ route('admin.administradores.index') }}">
                                <i class="fas fa-user-shield fa-fw me-2"></i> Administradores
                            </a>
                        </li>
                        <li class="nav-item border-top mt-3 pt-3">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="nav-link rounded text-danger bg-transparent border-0 w-100 text-start">
                                    <i class="fas fa-sign-out-alt fa-fw me-2"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <h1 class="h2">@yield('title', 'Dashboard')</h1>
                    @yield('actions')
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.datatable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
