@extends('layouts.admin')

@section('title', isset($producto) ? 'Editar Producto: ' . $producto->nombre : 'Nuevo Producto')

@section('actions')
<a href="{{ route('admin.productos.index') }}" class="btn btn-outline-secondary fw-bold rounded-pill px-4">
    <i class="fas fa-arrow-left me-2"></i> Volver al Catálogo
</a>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4 max-w-4xl mx-auto">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="mb-0 fw-bold"><i class="fas fa-shirt text-primary me-2"></i> {{ isset($producto) ? 'Modificar Información del Producto' : 'Registro de Nuevo Producto' }}</h5>
    </div>
    <div class="card-body p-4 p-md-5">
        <form action="{{ isset($producto) ? route('admin.productos.update', $producto->producto_id) : route('admin.productos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($producto))
                @method('PUT')
            @endif

            <div class="row g-4 mb-4">
                <div class="col-md-8">
                    <label for="nombre" class="form-label fw-bold">Nombre del Producto *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre', $producto->nombre ?? '') }}" required placeholder="Ej. Albacora Entera">
                </div>
                
                <div class="col-md-4">
                    <label for="categoria_id" class="form-label fw-bold">Categoría *</label>
                    <select class="form-select" id="categoria_id" name="categoria_id" required>
                        <option value="">-- Seleccione --</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->categoria_id }}" {{ old('categoria_id', $producto->categoria_id ?? '') == $cat->categoria_id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label for="descripcion" class="form-label fw-bold">Descripción Corta</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="2" placeholder="Breve detalle del producto que verá el cliente...">{{ old('descripcion', $producto->descripcion ?? '') }}</textarea>
                </div>

                <div class="col-md-6">
                    <label for="tipo_prenda" class="form-label fw-bold">Tipo de prenda *</label>
                    <select class="form-select" id="tipo_prenda" name="tipo_prenda" required>
                        @foreach(\App\Models\Producto::TIPOS_PRENDA as $clave => $etiqueta)
                            <option value="{{ $clave }}" {{ old('tipo_prenda', $producto->tipo_prenda ?? 'terno_2p') === $clave ? 'selected' : '' }}>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="publico" class="form-label fw-bold">Público *</label>
                    <select class="form-select" id="publico" name="publico" required>
                        @foreach(\App\Models\Producto::PUBLICOS as $clave => $etiqueta)
                            <option value="{{ $clave }}" {{ old('publico', $producto->publico ?? 'caballero') === $clave ? 'selected' : '' }}>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h6 class="fw-bold border-bottom pb-2 mb-4 text-primary mt-5"><i class="fas fa-boxes me-2"></i> Inventario y Precios</h6>
            
            <div class="row g-4 mb-4 bg-light p-3 rounded-4">
                <div class="col-md-6">
                    <label for="precio" class="form-label fw-bold d-block text-success">Precio Unitario *</label>
                     <div class="input-group">
                        <span class="input-group-text bg-success text-white"><i class="fas fa-dollar-sign"></i></span>
                        <input type="number" step="0.01" min="0" class="form-control" id="precio" name="precio" value="{{ old('precio', $producto->precio ?? '') }}" required placeholder="0.00">
                    </div>
                </div>
                @if(isset($producto))
                    <div class="col-md-6">
                        <label class="form-label fw-bold d-block text-secondary">Stock total</label>
                        <div class="form-control-plaintext fw-bold">{{ $producto->stock_total }} u</div>
                        <div class="form-text">Se gestiona por talla en la tabla de abajo.</div>
                    </div>
                @endif
            </div>

            <h6 class="fw-bold border-bottom pb-2 mb-4 text-primary mt-5"><i class="fas fa-ruler-combined me-2"></i> Modos de venta</h6>

            <div class="row g-4 mb-4 bg-light p-3 rounded-4">
                @if($ventaPorTalla)
                <div class="col-md-3 d-flex align-items-center">
                    <div class="form-check">
                        <input type="hidden" name="vende_por_talla" value="0">
                        <input class="form-check-input" type="checkbox" id="vende_por_talla" name="vende_por_talla" value="1" {{ old('vende_por_talla', $producto->vende_por_talla ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="vende_por_talla">Vender por talla</label>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-center">
                    <div class="form-check">
                        <input type="hidden" name="vende_a_medida" value="0">
                        <input class="form-check-input" type="checkbox" id="vende_a_medida" name="vende_a_medida" value="1" {{ old('vende_a_medida', $producto->vende_a_medida ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="vende_a_medida">Vender a medida</label>
                    </div>
                </div>
                @else
                <div class="col-12">
                    <input type="hidden" name="vende_por_talla" value="0">
                    <input type="hidden" name="vende_a_medida" value="1">
                    <p class="mb-0 small text-muted"><i class="fas fa-info-circle me-1"></i> La tienda está en modo solo a medida. Para vender por talla, activa el interruptor en Configuración → Negocio.</p>
                </div>
                @endif
                <div class="col-md-3">
                    <label for="precio_medida" class="form-label fw-bold d-block text-secondary">Precio a medida</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        <input type="number" step="0.01" min="0" class="form-control @error('precio_medida') is-invalid @enderror" id="precio_medida" name="precio_medida" value="{{ old('precio_medida', $producto->precio_medida ?? '') }}" placeholder="0.00">
                        @error('precio_medida')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="dias_confeccion" class="form-label fw-bold d-block text-secondary">Días de confección</label>
                    <input type="number" step="1" min="1" max="90" class="form-control @error('dias_confeccion') is-invalid @enderror" id="dias_confeccion" name="dias_confeccion" value="{{ old('dias_confeccion', $producto->dias_confeccion ?? '') }}">
                    @error('dias_confeccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <h6 class="fw-bold border-bottom pb-2 mb-4 text-primary mt-5"><i class="fas fa-image me-2"></i> Fotografía del Producto</h6>

            <div class="row g-4 align-items-center">
                @if(isset($producto) && $producto->imagen_url)
                    <div class="col-md-2 text-center">
                        <img src="{{ asset($producto->imagen_url) }}" alt="Producto actual" class="img-thumbnail shadow-sm rounded-4 bg-white" style="max-height: 120px; width: 100%; object-fit: cover;">
                        <span class="d-block small text-muted mt-2">Imagen actual</span>
                    </div>
                @endif
                <div class="col-md-{{ isset($producto) && $producto->imagen_url ? '10' : '12' }}">
                    <label for="imagen" class="form-label fw-bold">{{ isset($producto) ? 'Cambiar Imagen (Opcional)' : 'Subir Imagen' }}</label>
                    <input class="form-control" type="file" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.gif">
                    <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> Formatos aceptados: JPG, PNG, GIF. Tamaño máximo: 2MB. Una imagen clara ayuda a las ventas.</div>
                </div>
            </div>

            <div class="text-end border-top pt-4 mt-5">
                <button type="reset" class="btn btn-light btn-lg px-4 me-2">Limpiar Campos</button>
                <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow">
                    <i class="fas fa-save me-2"></i> {{ isset($producto) ? 'Guardar Cambios' : 'Registrar Producto' }}
                </button>
            </div>
        </form>
    </div>
</div>

@yield('variantes')
@yield('galeria')
@endsection
