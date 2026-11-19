@extends('layouts.app')

@section('title', $producto->nombre)

@section('content')
<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="fas fa-home"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('home', ['categoria' => $producto->categoria_id]) }}" class="text-decoration-none text-muted">{{ $producto->categoria->nombre ?? 'Catálogo' }}</a></li>
            <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">{{ $producto->nombre }}</li>
        </ol>
    </nav>

    <div class="row g-4 mb-5">
        <!-- Columna Izquierda: Imagen Grande -->
        <div class="col-md-6 col-xl-5">
            <div class="bg-white rounded-4 shadow-sm border p-4 text-center position-relative h-100 d-flex align-items-center justify-content-center" style="min-height: 450px;">
                <img src="{{ asset($producto->imagen_url ?? 'img/placeholder.svg') }}" 
                     alt="{{ $producto->nombre }}" 
                     class="img-fluid rounded-3" 
                     style="max-height: 500px; object-fit: contain;">
                
                @if($producto->stock_total <= 0)
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 rounded-4 d-flex justify-content-center align-items-center">
                        <span class="badge bg-danger fs-3 py-3 px-5 shadow border border-light border-3">Agotado</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Columna Central: Información del Producto -->
        <div class="col-md-6 col-xl-4 d-flex flex-column">
            <div class="mb-3">
                <h1 class="display-6 fw-bolder mb-2 text-dark" style="line-height: 1.2;">{{ $producto->nombre }}</h1>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-primary px-3 py-2 rounded-1 fw-bold">
                        <i class="fas fa-tag me-1"></i> {{ $producto->categoria->nombre ?? 'General' }}
                    </span>
                    <span class="text-muted small">
                        <i class="fas fa-star text-warning"></i> 
                        <i class="fas fa-star text-warning"></i> 
                        <i class="fas fa-star text-warning"></i> 
                        <i class="fas fa-star text-warning"></i> 
                        <i class="fas fa-star-half-alt text-warning"></i> 
                        (4.8)
                    </span>
                </div>
            </div>

            <hr class="text-muted opacity-25 my-1">
            
            <div class="my-4">
                <div class="d-flex align-items-baseline mb-2">
                    <span class="display-5 fw-bold text-dark">${{ number_format($producto->precio, 2) }}</span>
                </div>
                <p class="text-secondary small mb-0"><i class="fas fa-info-circle me-1"></i> Impuestos calculados en el checkout.</p>
            </div>

            <div class="mb-4 flex-grow-1">
                <h6 class="fw-bold text-dark mb-2">Acerca de este producto</h6>
                <p class="text-muted" style="line-height: 1.6; font-size: 0.95rem;">
                    {{ $producto->descripcion }}
                </p>
                <ul class="list-unstyled mt-3 text-muted" style="font-size: 0.9rem;">
                    <li class="mb-2 d-flex align-items-start"><i class="fas fa-check text-success mt-1 me-2"></i> <span><strong>Garantía de Frescura:</strong> Cadenas de frío monitoreadas.</span></li>
                    <li class="mb-2 d-flex align-items-start"><i class="fas fa-check text-success mt-1 me-2"></i> <span><strong>Calidad Premium:</strong> Selección manual rigurosa en puerto.</span></li>
                    <li class="mb-2 d-flex align-items-start"><i class="fas fa-check text-success mt-1 me-2"></i> <span><strong>Listo para Cocinar:</strong> Empacado al vacío.</span></li>
                </ul>
            </div>
        </div>

        <!-- Columna Derecha: Panel de Compra (Buy Box) -->
        <div class="col-12 col-xl-3">
            <div class="card border-1 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <h5 class="fw-bold mb-3 text-dark">Panel de Compra</h5>
                    
                    @if($variantes->isNotEmpty())
                        <div class="mb-3">
                            <span class="d-block text-success fw-bold mb-1 fs-5">
                                <i class="fas fa-check-circle me-1"></i> Disponible
                            </span>
                            <span class="text-muted small">
                                Stock total: <strong>{{ $producto->stock_total }} u</strong>
                            </span>
                        </div>

                        @auth
                            @if(auth()->user()->esCliente())
                                <form action="{{ route('carrito.agregar') }}" method="POST" class="mt-auto">
                                    @csrf

                                    <label class="form-label text-muted small fw-bold mb-1">Talla y color *</label>
                                    <div class="d-grid gap-2 mb-3">
                                        @foreach($variantes as $variante)
                                            <input type="radio" class="btn-check" name="variante_id"
                                                   id="variante-{{ $variante->variante_id }}"
                                                   value="{{ $variante->variante_id }}"
                                                   {{ $loop->first ? 'checked' : '' }} required>
                                            <label class="btn btn-outline-primary text-start" for="variante-{{ $variante->variante_id }}">
                                                <strong>Talla {{ $variante->talla }}</strong>@if($variante->color) · {{ $variante->color }}@endif
                                                <span class="d-block small text-muted">{{ $variante->stock }} disponibles</span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <label class="form-label text-muted small fw-bold mb-1">Cantidad (u):</label>
                                    <div class="input-group mb-3">
                                        <button class="btn btn-outline-secondary px-3" type="button" onclick="document.getElementById('cantidad_input').stepDown()"><i class="fas fa-minus"></i></button>
                                        <input type="number" name="cantidad" id="cantidad_input" class="form-control text-center fw-bold" value="1" min="1" max="{{ $variantes->max('stock') }}" style="max-width: 80px;">
                                        <button class="btn btn-outline-secondary px-3" type="button" onclick="document.getElementById('cantidad_input').stepUp()"><i class="fas fa-plus"></i></button>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold rounded-pill shadow-sm">
                                        <i class="fas fa-cart-shopping me-2"></i> Agregar al Carrito
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-secondary border-0 mt-auto mb-0 pt-3 pb-3 rounded-3 text-center">
                                    <i class="fas fa-user-shield fs-4 mb-2 d-block text-secondary"></i>
                                    <span class="small fw-bold">Modo Administrador</span>
                                </div>
                            @endif
                        @else
                            <div class="mt-auto">
                                <a href="{{ route('login') }}" class="btn btn-primary w-100 fw-bold rounded-pill py-2 shadow-sm mb-2">
                                    Iniciar Sesión
                                </a>
                                <a href="{{ route('register') }}" class="btn btn-outline-secondary w-100 fw-bold rounded-pill py-2">
                                    Crear Cuenta
                                </a>
                            </div>
                        @endauth

                        <div class="mt-4 pt-3 border-top text-center text-muted small">
                            <i class="fas fa-lock me-1"></i> Transacción segura SSL
                        </div>
                    @else
                        <div class="mt-auto text-center">
                            <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-50"></i>
                            <h5 class="text-danger fw-bold">Agotado</h5>
                            <p class="text-muted small">Te notificaremos cuando vuelva a estar disponible.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
