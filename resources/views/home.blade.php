@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<!-- Hero Section -->
<div class="row align-items-center mb-5 hero-panel text-white rounded-4 overflow-hidden shadow-lg position-relative">
    <div class="col-md-6 p-5">
        <h1 class="display-4 fw-bold mb-3 hero-title">Del Mar a tu Mesa</h1>
        <p class="lead mb-4">Los mariscos más frescos y de la más alta calidad, entregados directo en tu puerta. 100% garantizado.</p>
        <a href="#catalogo" class="btn btn-primary btn-lg fw-bold px-4 shadow">Ver Productos <i class="fas fa-arrow-right ms-2"></i></a>
    </div>
    <div class="col-md-6 p-0 h-100 position-relative d-none d-md-block" style="min-height: 400px;">
        <img src="{{ asset('img/placeholder.svg') }}" class="position-absolute w-100 h-100 object-fit-cover" alt="Imagen">
        <div class="bg-gradient-hero position-absolute w-100 h-100"></div>
    </div>
</div>

<!-- Filtros de Categoría -->
<div class="text-center mb-5" id="catalogo">
    <h2 class="fw-bold mb-4 position-relative d-inline-block pb-2">
        @if(request()->has('buscar'))
            Resultados para: "{{ request('buscar') }}"
        @elseif(isset($categoria_actual))
            Categoría: {{ $categoria_actual->nombre }}
        @else
            Nuestro Catálogo
        @endif
        <span class="position-absolute bottom-0 start-50 translate-middle-x bg-brand" style="height: 3px; width: 50%;"></span>
    </h2>
    <div class="d-flex justify-content-center gap-2 flex-wrap">
        <a href="{{ route('home') }}#catalogo" class="btn {{ !isset($categoria_actual) && !request()->has('buscar') ? 'cat' : 'btn-outline-primary' }} rounded-pill px-4 shadow-sm">Todos</a>
        @foreach($categorias as $cat)
            <a href="{{ route('home', ['categoria' => $cat->categoria_id, 'buscar' => request('buscar')]) }}#catalogo" class="btn {{ isset($categoria_actual) && $categoria_actual->categoria_id == $cat->categoria_id ? 'cat' : 'btn-outline-primary' }} rounded-pill px-4 shadow-sm">{{ $cat->nombre }}</a>
        @endforeach
    </div>
</div>

<!-- Grid de Productos -->
<div class="row g-4">
    @forelse($productos as $producto)
        <div class="col-sm-6 col-md-4 col-xl-3">
            <div class="card h-100 shadow-sm border-0 product-card overflow-hidden rounded-4">
                <div class="position-relative overflow-hidden">
                    <a href="{{ route('productos.show', $producto->producto_id) }}">
                        <img src="{{ asset($producto->imagen_url ?? 'img/placeholder.svg') }}" class="card-img-top object-fit-cover" style="height: 220px; transition: transform 0.5s ease;" alt="{{ $producto->nombre }}">
                    </a>
                    @if($producto->stock <= 0)
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex justify-content-center align-items-center">
                            <span class="badge bg-danger fs-5 px-3 py-2">Agotado</span>
                        </div>
                    @endif
                    <div class="position-absolute top-0 end-0 m-2">
                        <span class="badge bg-white text-primary shadow-sm px-2 py-1 small fw-bold">{{ $producto->categoria->nombre ?? 'Sin Categoría' }}</span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column p-4">
                    <a href="{{ route('productos.show', $producto->producto_id) }}" class="text-decoration-none">
                        <h5 class="card-title fw-bold fs-5 mb-2 text-dark product-title-hover" title="{{ $producto->nombre }}">{{ $producto->nombre }}</h5>
                    </a>
                    <p class="card-text text-muted small mb-3 flex-grow-1 text-truncate-2" style="min-height: 40px;">
                        {{ $producto->descripcion }}
                    </p>
                    <div class="d-flex justify-content-between align-items-baseline mt-auto">
                        <div class="d-flex align-items-baseline">
                            <span class="fs-3 fw-bold text-primary">${{ number_format($producto->precio, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pt-0 pb-3 mt-2 px-3">
                    @if($producto->stock > 0)
                        @auth
                            @if(auth()->user()->esCliente())
                                <form action="{{ route('carrito.agregar') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="producto_id" value="{{ $producto->producto_id }}">
                                    <div class="input-group mb-2">
                                        <input type="number" name="cantidad" class="form-control form-control-sm text-center" value="1" min="1" max="{{ $producto->stock }}">
                                        <span class="input-group-text">u</span>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                                        <i class="fas fa-cart-plus me-1"></i> Agregar
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary w-100 fw-bold">Modo Admin</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary w-100 fw-bold">Inicia sesión para comprar</a>
                        @endauth
                    @else
                        <button class="btn btn-secondary w-100 fw-bold" disabled>No Disponible</button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
            <h3>No hay productos disponibles</h3>
            <p class="text-muted">Próximamente agregaremos más productos a nuestro catálogo.</p>
        </div>
    @endforelse
</div>
@endsection
