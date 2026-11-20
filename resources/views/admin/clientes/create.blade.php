@extends('layouts.admin')

@section('title', isset($cliente) ? 'Editar Cliente' : 'Nuevo Cliente')

@section('actions')
<a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary fw-bold rounded-pill px-4">
    <i class="fas fa-arrow-left me-2"></i> Volver a Clientes
</a>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4 max-w-4xl mx-auto">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="mb-0 fw-bold"><i class="fas fa-user-edit text-primary me-2"></i> {{ isset($cliente) ? 'Actualizar Ficha de Cliente' : 'Registrar Nuevo Cliente' }}</h5>
    </div>
    <div class="card-body p-4 p-md-5">
        <form action="{{ isset($cliente) ? route('admin.clientes.update', $cliente->usuario_id) : route('admin.clientes.store') }}" method="POST">
            @csrf
            @if(isset($cliente))
                @method('PUT')
            @endif

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombres y Apellidos *</label>
                    <input type="text" class="form-control" name="nombre_completo" value="{{ old('nombre_completo', $cliente->nombre_completo ?? '') }}" required placeholder="Ej. Juan Pérez">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold d-flex justify-content-between">
                        <span>Nombre de Usuario (Login) *</span>
                        @if(isset($cliente))
                            <span class="badge bg-light text-muted fw-normal">Inmutable si se desea</span>
                        @endif
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-user-circle text-muted"></i></span>
                        <input type="text" class="form-control" name="nombre_usuario" value="{{ old('nombre_usuario', $cliente->nombre_usuario ?? '') }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Correo Electrónico *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control" name="correo" value="{{ old('correo', $cliente->correo ?? '') }}" required placeholder="ejemplo@correo.com">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Teléfono de Contacto</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-phone-alt text-muted"></i></span>
                        <input type="text" class="form-control" name="telefono" value="{{ old('telefono', $cliente->telefono ?? '') }}" maxlength="10" placeholder="099xxxxxxx">
                    </div>
                </div>

                <div class="col-12 border-top pt-4">
                    <label class="form-label fw-bold"><i class="fas fa-map-marker-alt text-danger me-1"></i> Dirección de Entrega *</label>
                    <input type="text" class="form-control" name="direccion" value="{{ old('direccion', $cliente->direccion ?? '') }}" required placeholder="Dirección completa y referencias">
                </div>
            </div>

            <div class="bg-light p-4 rounded-4 mt-5 border border-warning border-opacity-25">
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-lock text-warning me-2"></i> Seguridad de la Cuenta</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-1">Contraseña {{ isset($cliente) ? '(Opcional)' : '*' }}</label>
                        @if(isset($cliente))
                            <small class="d-block text-muted mb-2 fst-italic">Deja vacío para mantener la actual</small>
                        @endif
                        <input type="password" class="form-control border-warning border-opacity-50" name="password" {{ isset($cliente) ? '' : 'required' }} minlength="8" placeholder="{{ isset($cliente) ? 'Nueva contraseña...' : 'Mínimo 8 caracteres' }}">
                    </div>
                </div>
            </div>

            <div class="text-end pt-4 mt-4">
                <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow">
                    <i class="fas fa-save me-2"></i> {{ isset($cliente) ? 'Guardar Cambios' : 'Registrar Cliente' }}
                </button>
            </div>
        </form>
    </div>
</div>

@yield('medidas')
@endsection
