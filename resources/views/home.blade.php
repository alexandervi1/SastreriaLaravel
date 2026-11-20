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
        @php
            $hero = is_file(public_path('img/productos/demo/demo-1.svg')) ? 'img/productos/demo/demo-1.svg' : 'img/placeholder.svg';
        @endphp
        <img src="{{ asset($hero) }}" alt="Terno de gala" class="w-100 h-100 object-fit-cover" style="min-height: 380px;">
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
                                <a href="{{ route('productos.show', $producto->slug) }}" class="btn btn-sm btn-outline-primary">
                                    @if($producto->vende_por_talla) Ver tallas @elseif($producto->vende_a_medida) Encargar @else Ver @endif
                                </a>
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
                            <a href="{{ route('productos.show', $producto->slug) }}" class="btn btn-sm btn-primary">
                                @if($producto->vende_por_talla)
                                    Elegir talla
                                @elseif($producto->vende_a_medida)
                                    Encargar a medida
                                @else
                                    Ver modelo
                                @endif
                            </a>
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
