@extends('layouts.app')

@section('title', 'Editar perfil de medidas')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-ruler-combined text-primary me-2"></i> Editar: {{ $medida->nombre }}</h5>
        <a href="{{ route('medidas.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Volver</a>
    </div>
    <div class="card-body p-4 p-md-5">
        <form action="{{ route('medidas.update', $medida->medida_id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('medidas._form')
            <div class="text-end border-top pt-4 mt-4">
                <a href="{{ route('medidas.index') }}" class="btn btn-light btn-lg px-4 me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow">
                    <i class="fas fa-save me-2"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
