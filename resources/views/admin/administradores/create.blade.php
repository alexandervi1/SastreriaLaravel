@extends('layouts.admin')

@section('title', isset($administrador) ? 'Editar Administrador' : 'Nuevo Administrador')

@section('actions')
<a href="{{ route('admin.administradores.index') }}" class="btn btn-outline-secondary fw-bold rounded-pill px-4">
    <i class="fas fa-arrow-left me-2"></i> Volver a Administradores
</a>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="mb-0 fw-bold"><i class="fas fa-user-shield text-primary me-2"></i> {{ isset($administrador) ? 'Actualizar Cuenta de Administrador' : 'Registrar Nuevo Administrador' }}</h5>
    </div>
    <div class="card-body p-4 p-md-5">
        <form action="{{ isset($administrador) ? route('admin.administradores.update', $administrador->usuario_id) : route('admin.administradores.store') }}" method="POST">
            @csrf
            @if(isset($administrador))
                @method('PUT')
            @endif

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombres y Apellidos *</label>
                    <input type="text" class="form-control" name="nombre_completo" value="{{ old('nombre_completo', $administrador->nombre_completo ?? '') }}" required placeholder="Ej. Nombre Apellido">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre de Usuario (Login) *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-user-circle text-muted"></i></span>
                        <input type="text" class="form-control" name="nombre_usuario" value="{{ old('nombre_usuario', $administrador->nombre_usuario ?? '') }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Correo Electrónico *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control" name="correo" value="{{ old('correo', $administrador->correo ?? '') }}" required placeholder="admin@ejemplo.com">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Teléfono de Contacto</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-phone-alt text-muted"></i></span>
                        <input type="text" class="form-control" name="telefono" value="{{ old('telefono', $administrador->telefono ?? '') }}" maxlength="10" placeholder="099xxxxxxx">
                    </div>
                </div>

                <div class="col-12 border-top pt-4">
                    <label class="form-label fw-bold"><i class="fas fa-map-marker-alt text-danger me-1"></i> Dirección *</label>
                    <input type="text" class="form-control" name="direccion" value="{{ old('direccion', $administrador->direccion ?? '') }}" required placeholder="Dirección del administrador">
                </div>
            </div>

            <div class="bg-light p-4 rounded-4 mt-5 border border-warning border-opacity-25">
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-lock text-warning me-2"></i> Seguridad de la Cuenta</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-1">Contraseña {{ isset($administrador) ? '(Opcional)' : '*' }}</label>
                        @if(isset($administrador))
                            <small class="d-block text-muted mb-2 fst-italic">Deja vacío para mantener la actual</small>
                        @endif
                        <input type="password" class="form-control border-warning border-opacity-50" name="password" {{ isset($administrador) ? '' : 'required' }} minlength="8" placeholder="{{ isset($administrador) ? 'Nueva contraseña...' : 'Mínimo 8 caracteres' }}">
                        <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> Al cambiarla se actualiza la fecha de rotación y desaparece la alerta de seguridad.</div>
                    </div>
                    @if(isset($administrador) && $administrador->usuario_id === auth()->id())
                        <div class="col-md-6">
                            <label class="form-label fw-bold mb-1">Contraseña Actual *</label>
                            <input type="password" class="form-control" name="current_password" placeholder="Confirma tu contraseña actual" autocomplete="current-password">
                            <div class="form-text mt-2">Obligatoria para cambiar tu propia contraseña.</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="text-end pt-4 mt-4">
                <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow">
                    <i class="fas fa-save me-2"></i> {{ isset($administrador) ? 'Guardar Cambios' : 'Registrar Administrador' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
