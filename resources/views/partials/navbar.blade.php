<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset('img/negocio/colibri-icono-oro.svg') }}" alt="" class="brand-icon" aria-hidden="true">
            <span class="brand-wordmark">{{ $negocio['nombre'] }}</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#a-medida">A medida</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#telas">Telas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('quienes.somos') ? 'active' : '' }}" href="{{ route('quienes.somos') }}">El taller</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('citas.*') ? 'active' : '' }}" href="{{ route('citas.create') }}">Citas</a>
                </li>
            </ul>

            <ul class="navbar-nav align-items-lg-center gap-lg-2">
                @guest
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link">Iniciar sesión</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="btn btn-outline-gold btn-sm px-3">Crear cuenta</a>
                    </li>
                @else
                    @if(auth()->user()->esCliente())
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="{{ route('carrito.index') }}" aria-label="Carrito">
                                <i class="fas fa-shopping-bag"></i>
                                @php
                                    $cartCount = \App\Models\Carrito::where('usuario_id', auth()->id())->sum('cantidad');
                                @endphp
                                @if($cartCount > 0)
                                    <span class="badge badge-cart ms-1">{{ $cartCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ auth()->user()->nombre_completo }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            @if(auth()->user()->esAdmin())
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fas fa-cogs me-2"></i> Panel de control</a></li>
                            @elseif(auth()->user()->esCliente())
                                <li><a class="dropdown-item" href="{{ route('pedidos.index') }}"><i class="fas fa-box-open me-2"></i> Mis pedidos</a></li>
                                <li><a class="dropdown-item" href="{{ route('medidas.index') }}"><i class="fas fa-ruler-combined me-2"></i> Mis medidas</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
