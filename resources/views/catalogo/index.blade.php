@extends('layouts.app')

@section('title', 'Catálogo')
@section('meta_description', 'Catálogo de prendas a medida de Entretela: ternos, sacos, abrigos, chalecos, pantalones y faldas para caballero, dama y niño.')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <p class="eyebrow mb-2">Hecho para ti</p>
        <h1 class="display-6 fw-bold mb-0">Catálogo</h1>
    </div>
    <form action="{{ route('catalogo') }}" method="GET" class="d-flex gap-2" style="max-width: 380px;">
        <input type="search" name="buscar" value="{{ request('buscar') }}" class="form-control" placeholder="Buscar un modelo…" aria-label="Buscar un modelo">
        <button class="btn btn-outline-gold px-3" type="submit" aria-label="Buscar"><i class="fas fa-search"></i></button>
    </form>
</div>

<div class="d-flex flex-wrap gap-3 align-items-center mb-4 small">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="text-muted fw-bold">Público:</span>
        <a href="{{ route('catalogo', array_filter(['buscar' => request('buscar'), 'tipo_prenda' => $tipoActual])) }}" class="btn btn-sm rounded-pill {{ $publicoActual ? 'btn-outline-secondary' : 'btn-secondary' }}">Todos</a>
        @foreach(\App\Models\Producto::PUBLICOS as $clave => $etiqueta)
            <a href="{{ route('catalogo', array_filter(['buscar' => request('buscar'), 'publico' => $clave, 'tipo_prenda' => $tipoActual])) }}" class="btn btn-sm rounded-pill {{ $publicoActual === $clave ? 'btn-secondary' : 'btn-outline-secondary' }}">{{ $etiqueta }}</a>
        @endforeach
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="text-muted fw-bold">Prenda:</span>
        <a href="{{ route('catalogo', array_filter(['buscar' => request('buscar'), 'publico' => $publicoActual])) }}" class="btn btn-sm rounded-pill {{ $tipoActual ? 'btn-outline-secondary' : 'btn-secondary' }}">Todas</a>
        @foreach(\App\Models\Producto::TIPOS_PRENDA as $clave => $etiqueta)
            <a href="{{ route('catalogo', array_filter(['buscar' => request('buscar'), 'publico' => $publicoActual, 'tipo_prenda' => $clave])) }}" class="btn btn-sm rounded-pill {{ $tipoActual === $clave ? 'btn-secondary' : 'btn-outline-secondary' }}">{{ $etiqueta }}</a>
        @endforeach
    </div>
</div>

<div class="row g-4">
    @forelse($productos as $producto)
        <div class="col-sm-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                <a href="{{ route('productos.show', $producto->slug) }}">
                    <img src="{{ asset($producto->imagen_url ?? 'img/placeholder.svg') }}" class="card-img-top object-fit-cover" style="height: 240px;" alt="{{ $producto->nombre }}">
                </a>
                <div class="card-body d-flex flex-column">
                    <h2 class="h6 fw-bold mb-1">{{ $producto->nombre }}</h2>
                    <p class="small text-muted mb-3">{{ $producto->tipo_prenda_label }} · {{ $producto->publico_label }}</p>
                    <div class="mt-auto d-flex justify-content-between align-items-center gap-2">
                        <span class="fw-bold">Hechura desde ${{ number_format((float) ($producto->precio_medida ?? $producto->precio), 2) }}</span>
                        <a href="{{ route('productos.show', $producto->slug) }}" class="btn btn-sm btn-primary">Encargar</a>
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
@endsection
