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
                    @if($producto->stock_total <= 0)
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
                    @if($producto->stock_total > 0)
                        @auth
                            @if(auth()->user()->esCliente())
                                <a href="{{ route('productos.show', $producto->slug) }}" class="btn btn-primary w-100 fw-bold shadow-sm">
                                    <i class="fas fa-ruler-combined me-1"></i> Elegir talla
                                </a>
                            @else
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary w-100 fw-bold">Modo Admin</a>
                            @endif
                        @else
                            <a href="{{ route('productos.show', $producto->slug) }}" class="btn btn-outline-primary w-100 fw-bold">Ver tallas</a>
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
@extends('layouts.app')

@section('title', 'Inicio')
@section('meta_description', 'Ternos de gala por talla y a medida. Elige modelo, tela y agenda tu cita en el taller.')
@section('og_title', $negocio['nombre'])

@section('content')
@php
    $diasNombres = [1 => 'lunes', 2 => 'martes', 3 => 'miércoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sábado', 7 => 'domingo'];
    $dias = array_map(fn ($d) => $diasNombres[$d] ?? $d, \App\Models\Configuracion::obtenerDiasTaller());
    $horario = \App\Models\Configuracion::obtenerHoraInicio() . ' a ' . \App\Models\Configuracion::obtenerHoraFin();
@endphp

<section class="row align-items-center g-0 mb-5 bg-white rounded-4 shadow-sm overflow-hidden">
    <div class="col-lg-6 p-5 p-lg-5">
        <h1 class="display-4 fw-bold mb-3">Trajes que se ajustan a la persona, no al contrario.</h1>
        <p class="lead text-muted mb-4">
            Ternos de gala por talla o confeccionados a medida en nuestro taller. Elige tela, toma tus medidas y recíbelo listo para la ocasión.
        </p>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('citas.create') }}" class="btn btn-primary btn-lg fw-bold px-4">
                <i class="fas fa-calendar-check me-2"></i> Agenda tu cita
            </a>
            <a href="#catalogo" class="btn btn-outline-primary btn-lg fw-bold px-4">Ver el catálogo</a>
        </div>
    </div>
    <div class="col-lg-6">
        <img src="{{ asset('img/placeholder.svg') }}" alt="Terno de gala" class="w-100 h-100 object-fit-cover" style="min-height: 380px;">
    </div>
</section>

@if($destacados->isNotEmpty())
    <section class="mb-5">
        <h2 class="fw-bold mb-4">Ternos destacados</h2>
        <div class="row g-4">
            @foreach($destacados as $producto)
                <div class="col-sm-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <img src="{{ asset($producto->imagen_url ?? 'img/placeholder.svg') }}" class="card-img-top object-fit-cover" style="height: 220px;" alt="{{ $producto->nombre }}">
                        <div class="card-body d-flex flex-column">
                            <h3 class="h6 fw-bold mb-1">{{ $producto->nombre }}</h3>
                            <p class="small text-muted mb-3">{{ $producto->categoria->nombre ?? '' }}</p>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary">${{ number_format((float) $producto->precio, 2) }}</span>
                                <a href="{{ route('productos.show', $producto->slug) }}" class="btn btn-sm btn-outline-primary">Ver tallas</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif

<section class="mb-5" id="catalogo">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <h2 class="fw-bold mb-0">
            @if(request()->has('buscar'))
                Resultados para «{{ request('buscar') }}»
            @elseif(isset($categoria_actual))
                {{ $categoria_actual->nombre }}
            @else
                Catálogo
            @endif
        </h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('home') }}#catalogo" class="btn btn-sm rounded-pill {{ !isset($categoria_actual) && !request()->has('buscar') ? 'btn-primary' : 'btn-outline-primary' }}">Todos</a>
            @foreach($categorias as $cat)
                <a href="{{ route('home', ['categoria' => $cat->categoria_id]) }}#catalogo" class="btn btn-sm rounded-pill {{ isset($categoria_actual) && $categoria_actual->categoria_id === $cat->categoria_id ? 'btn-primary' : 'btn-outline-primary' }}">{{ $cat->nombre }}</a>
            @endforeach
        </div>
    </div>

    <div class="row g-4">
        @forelse($productos as $producto)
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="position-relative">
                        <a href="{{ route('productos.show', $producto->slug) }}">
                            <img src="{{ asset($producto->imagen_url ?? 'img/placeholder.svg') }}" class="card-img-top object-fit-cover" style="height: 240px;" alt="{{ $producto->nombre }}">
                        </a>
                        @if($producto->stock_total <= 0 && ! $producto->vende_a_medida)
                            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center">
                                <span class="badge bg-danger fs-6">Agotado</span>
                            </div>
                        @endif
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h3 class="h6 fw-bold mb-1">{{ $producto->nombre }}</h3>
                        <p class="small text-muted mb-3">{{ $producto->descripcion }}</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center gap-2">
                            <span class="fw-bold">${{ number_format((float) $producto->precio, 2) }}</span>
                            <a href="{{ route('productos.show', $producto->slug) }}" class="btn btn-sm btn-primary">Elegir talla</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fas fa-shirt fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">No encontramos modelos con ese filtro.</p>
            </div>
        @endforelse
    </div>
</section>

<section class="mb-5">
    <h2 class="fw-bold mb-4">Así funciona tu terno a medida</h2>
    <div class="row g-4">
        @foreach([
            ['Eliges modelo y tela', 'Escoge el corte y la tela en la ficha del terno.'],
            ['Tomamos tus medidas', 'En el taller o con un perfil guardado en tu cuenta.'],
            ['Prueba', 'Ajustamos el terno contigo antes de la entrega.'],
            ['Entrega', 'Retiras en el taller o lo enviamos a tu dirección.'],
        ] as $i => $paso)
            <div class="col-sm-6 col-lg-3">
                <div class="h-100 border rounded-4 p-4 bg-white">
                    <div class="fs-4 fw-bold text-primary mb-2">{{ $i + 1 }}</div>
                    <h3 class="h6 fw-bold">{{ $paso[0] }}</h3>
                    <p class="small text-muted mb-0">{{ $paso[1] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

<section class="bg-white rounded-4 shadow-sm p-4 p-md-5">
    <div class="row g-4 align-items-center">
        <div class="col-md-7">
            <h2 class="fw-bold mb-3">Visítanos</h2>
            <p class="mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i>{{ $negocio['direccion'] ?: 'Dirección pendiente de configurar' }}</p>
            <p class="mb-2"><i class="fas fa-clock text-primary me-2"></i>{{ ucfirst(implode(', ', $dias)) }}, {{ $horario }}</p>
            <p class="mb-0"><i class="fas fa-phone text-primary me-2"></i>{{ $negocio['telefono'] ?: 'Teléfono pendiente de configurar' }}</p>
        </div>
        <div class="col-md-5 text-md-end">
            <a href="{{ route('citas.create') }}" class="btn btn-primary btn-lg fw-bold">Reservar una cita</a>
        </div>
    </div>
</section>
@endsection
