@extends('layouts.admin')

@section('title', 'Telas')

@section('actions')
<a href="{{ route('admin.telas.create') }}" class="btn btn-primary fw-bold rounded-pill px-4">
    <i class="fas fa-plus me-2"></i> Nueva tela
</a>
@endsection

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-scroll text-primary me-2"></i> Catálogo de telas</h5>
        <span class="badge bg-light text-muted fw-normal">{{ $telas->count() }} {{ \Illuminate\Support\Str::plural('registro', $telas->count()) }}</span>
    </div>
    <div class="card-body p-0">
        @if($telas->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-scroll fa-4x text-muted mb-3 opacity-50"></i>
                <h5 class="text-muted">No hay telas registradas.</h5>
                <p class="text-muted">Agrega la primera para habilitar la confección a medida.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Tela</th>
                            <th>Composición</th>
                            <th>Color</th>
                            <th class="text-end">Recargo</th>
                            <th class="text-center">Activa</th>
                            <th class="text-center pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($telas as $tela)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset($tela->imagen_url ?? 'img/placeholder.svg') }}" alt="{{ $tela->nombre }}" class="rounded-3 me-3 object-fit-cover border" style="width: 48px; height: 48px;">
                                        <span class="fw-bold">{{ $tela->nombre }}</span>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $tela->composicion ?? '—' }}</td>
                                <td>{{ $tela->color }}</td>
                                <td class="text-end">${{ number_format((float) $tela->recargo, 2) }}</td>
                                <td class="text-center">
                                    @if($tela->activo)
                                        <span class="badge bg-success rounded-pill">Sí</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill">No</span>
                                    @endif
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.telas.edit', $tela->tela_id) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.telas.destroy', $tela->tela_id) }}" method="POST" class="m-0 p-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Eliminar esta tela?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
