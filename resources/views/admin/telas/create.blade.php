@extends('layouts.admin')

@section('title', isset($tela) ? 'Editar tela: ' . $tela->nombre : 'Nueva tela')

@section('actions')
<a href="{{ route('admin.telas.index') }}" class="btn btn-outline-secondary fw-bold rounded-pill px-4">
    <i class="fas fa-arrow-left me-2"></i> Volver a telas
</a>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4 max-w-4xl mx-auto">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="mb-0 fw-bold"><i class="fas fa-scroll text-primary me-2"></i> {{ isset($tela) ? 'Modificar tela' : 'Registrar tela' }}</h5>
    </div>
    <div class="card-body p-4 p-md-5">
        <form action="{{ isset($tela) ? route('admin.telas.update', $tela->tela_id) : route('admin.telas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($tela))
                @method('PUT')
            @endif

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label for="nombre" class="form-label fw-bold">Nombre *</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre', $tela->nombre ?? '') }}" required maxlength="80" placeholder="Lana merino azul">
                    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="color" class="form-label fw-bold">Color *</label>
                    <input type="text" class="form-control @error('color') is-invalid @enderror" id="color" name="color" value="{{ old('color', $tela->color ?? '') }}" required maxlength="40" placeholder="Azul noche">
                    @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-8">
                    <label for="composicion" class="form-label fw-bold">Composición</label>
                    <input type="text" class="form-control @error('composicion') is-invalid @enderror" id="composicion" name="composicion" value="{{ old('composicion', $tela->composicion ?? '') }}" maxlength="120" placeholder="100 % lana merino">
                    @error('composicion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="recargo" class="form-label fw-bold">Recargo *</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0" class="form-control @error('recargo') is-invalid @enderror" id="recargo" name="recargo" value="{{ old('recargo', $tela->recargo ?? '0') }}" required>
                        @error('recargo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-8">
                    <label for="imagen" class="form-label fw-bold">{{ isset($tela) ? 'Cambiar imagen' : 'Imagen' }}</label>
                    <input class="form-control @error('imagen') is-invalid @enderror" type="file" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.webp">
                    <div class="form-text">JPG, PNG o WebP, máximo 2 MB y 4000×4000 px.</div>
                    @error('imagen')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4 d-flex align-items-center">
                    <div class="form-check">
                        <input type="hidden" name="activo" value="0">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" {{ old('activo', $tela->activo ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="activo">Tela activa</label>
                    </div>
                </div>

                @if(isset($tela) && $tela->imagen_url)
                    <div class="col-12">
                        <img src="{{ asset($tela->imagen_url) }}" alt="{{ $tela->nombre }}" class="rounded-3 border" style="max-height: 160px;">
                    </div>
                @endif
            </div>

            <div class="text-end border-top pt-4">
                <a href="{{ route('admin.telas.index') }}" class="btn btn-light btn-lg px-4 me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow">
                    <i class="fas fa-save me-2"></i> {{ isset($tela) ? 'Guardar cambios' : 'Registrar tela' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
