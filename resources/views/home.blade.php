@extends('layouts.app')

@section('title', 'Sastrería de gala a medida')
@section('meta_description', 'Sastrería de gala a medida en Riobamba. Elige la prenda y la tela, toma tus medidas y recibe una pieza hecha para ti.')
@section('og_title', $negocio['nombre'])

@section('content')
@php
    $diasNombres = [1 => 'lunes', 2 => 'martes', 3 => 'miércoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sábado', 7 => 'domingo'];
    $dias = array_map(fn ($d) => $diasNombres[$d] ?? $d, \App\Models\Configuracion::obtenerDiasTaller());
    $horario = \App\Models\Configuracion::obtenerHoraInicio() . ' a ' . \App\Models\Configuracion::obtenerHoraFin();
@endphp

<section class="hero-entretela row align-items-center g-0 mb-5 overflow-hidden">
    <div class="col-lg-6 p-4 p-lg-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <img src="{{ asset('img/negocio/colibri-icono-oro.svg') }}" alt="" class="hero-bird" aria-hidden="true">
            <span class="eyebrow">Sastrería de gala · Riobamba</span>
        </div>
        <h1 class="hero-title mb-3">Tu noche, <em>a medida.</em></h1>
        <p class="hero-lead mb-4">
            Lo que no se ve es lo que da forma. Elegimos contigo la prenda y la tela,
            tomamos tus medidas y cosemos una pieza que solo te pertenece.
        </p>
        <div class="d-flex flex-wrap gap-3">
            <a href="{{ route('citas.create') }}" class="btn btn-primary btn-lg px-4">
                Reservar cita de medidas
            </a>
            <a href="#catalogo" class="btn btn-outline-gold btn-lg px-4">Ver modelos</a>
        </div>
    </div>
    <div class="col-lg-6 hero-image-wrap">
        @php
            $hero = is_file(public_path('img/productos/demo/demo-1.svg')) ? 'img/productos/demo/demo-1.svg' : 'img/placeholder.svg';
        @endphp
        <img src="{{ asset($hero) }}" alt="Terno de gala confeccionado a medida" class="w-100 h-100 object-fit-cover">
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
                                @if($producto->vende_a_medida)
                                    <span class="fw-bold text-primary">Hechura desde ${{ number_format((float) ($producto->precio_medida ?? $producto->precio), 2) }}</span>
                                @else
                                    <span class="fw-bold text-primary">${{ number_format((float) $producto->precio, 2) }}</span>
                                @endif
                                <a href="{{ route('productos.show', $producto->slug) }}" class="btn btn-sm btn-outline-primary">
                                    @if($producto->vende_por_talla && \App\Models\Configuracion::ventaPorTallaActiva()) Ver tallas @elseif($producto->vende_a_medida) Encargar @else Ver @endif
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
    <form action="{{ route('home') }}" method="GET" class="d-flex gap-2 mb-4" style="max-width: 480px;">
        <input type="search" name="buscar" value="{{ request('buscar') }}" class="form-control" placeholder="Buscar un modelo…" aria-label="Buscar un modelo">
        <button class="btn btn-outline-gold px-3" type="submit" aria-label="Buscar"><i class="fas fa-search"></i></button>
    </form>
    <div class="mb-4">
        <h2 class="fw-bold mb-3">Encuentra tu prenda</h2>
        <div class="row g-3">
            @foreach(\App\Models\Producto::PUBLICOS as $clave => $etiqueta)
                <div class="col-md-4">
                    <a href="{{ route('home', ['publico' => $clave]) }}#catalogo"
                       class="d-flex align-items-center justify-content-between text-decoration-none rounded-4 border bg-white p-4 h-100 shadow-sm">
                        <span class="fw-bold text-dark">{{ $etiqueta }}</span>
                        <i class="fas fa-arrow-right text-primary"></i>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

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
            <a href="{{ route('home', array_filter(['publico' => $publicoActual, 'tipo_prenda' => $tipoActual])) }}#catalogo" class="btn btn-sm rounded-pill {{ !isset($categoria_actual) && !request()->has('buscar') ? 'btn-primary' : 'btn-outline-primary' }}">Todos</a>
            @foreach($categorias as $cat)
                <a href="{{ route('home', array_filter(['categoria' => $cat->categoria_id, 'publico' => $publicoActual, 'tipo_prenda' => $tipoActual])) }}#catalogo" class="btn btn-sm rounded-pill {{ isset($categoria_actual) && $categoria_actual->categoria_id === $cat->categoria_id ? 'btn-primary' : 'btn-outline-primary' }}">{{ $cat->nombre }}</a>
            @endforeach
        </div>
    </div>

    <div class="d-flex flex-wrap gap-3 align-items-center mb-4 small">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="text-muted fw-bold">Público:</span>
            <a href="{{ route('home', array_filter(['categoria' => request('categoria'), 'buscar' => request('buscar'), 'tipo_prenda' => $tipoActual])) }}#catalogo" class="btn btn-sm rounded-pill {{ $publicoActual ? 'btn-outline-secondary' : 'btn-secondary' }}">Todos</a>
            @foreach(\App\Models\Producto::PUBLICOS as $clave => $etiqueta)
                <a href="{{ route('home', array_filter(['categoria' => request('categoria'), 'buscar' => request('buscar'), 'publico' => $clave, 'tipo_prenda' => $tipoActual])) }}#catalogo" class="btn btn-sm rounded-pill {{ $publicoActual === $clave ? 'btn-secondary' : 'btn-outline-secondary' }}">{{ $etiqueta }}</a>
            @endforeach
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="text-muted fw-bold">Prenda:</span>
            <a href="{{ route('home', array_filter(['categoria' => request('categoria'), 'buscar' => request('buscar'), 'publico' => $publicoActual])) }}#catalogo" class="btn btn-sm rounded-pill {{ $tipoActual ? 'btn-outline-secondary' : 'btn-secondary' }}">Todas</a>
            @foreach(\App\Models\Producto::TIPOS_PRENDA as $clave => $etiqueta)
                <a href="{{ route('home', array_filter(['categoria' => request('categoria'), 'buscar' => request('buscar'), 'publico' => $publicoActual, 'tipo_prenda' => $clave])) }}#catalogo" class="btn btn-sm rounded-pill {{ $tipoActual === $clave ? 'btn-secondary' : 'btn-outline-secondary' }}">{{ $etiqueta }}</a>
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
                        <p class="small text-muted mb-3">{{ $producto->tipo_prenda_label }} · {{ $producto->publico_label }}</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center gap-2">
                            @if($producto->vende_a_medida)
                                <span class="fw-bold">Hechura desde ${{ number_format((float) ($producto->precio_medida ?? $producto->precio), 2) }}</span>
                            @else
                                <span class="fw-bold">${{ number_format((float) $producto->precio, 2) }}</span>
                            @endif
                            <a href="{{ route('productos.show', $producto->slug) }}" class="btn btn-sm btn-primary">
                                @if($producto->vende_por_talla && \App\Models\Configuracion::ventaPorTallaActiva())
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

<section class="mb-5" id="telas">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <span class="eyebrow">Materia prima</span>
            <h2 class="fw-bold mb-0 mt-1">Telas</h2>
        </div>
        <a href="{{ route('ayuda') }}" class="text-gold small">Cómo elegir una tela <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-3">
        @forelse($telas as $tela)
            <div class="col-sm-6 col-lg-4">
                <div class="tela-card h-100 p-4">
                    <h3 class="h6 fw-bold mb-1">{{ $tela->nombre }}</h3>
                    <p class="small text-muted mb-2">{{ $tela->composicion ?: 'Composición a consultar' }}@if($tela->color) · {{ $tela->color }}@endif</p>
                    <p class="small mb-0 text-gold">Tela +${{ number_format((float) $tela->recargo, 2) }}</p>
                </div>
            </div>
        @empty
            <div class="col-12"><p class="text-muted">Las telas se muestran al elegir un modelo.</p></div>
        @endforelse
    </div>
</section>

<section class="mb-5" id="a-medida">
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
