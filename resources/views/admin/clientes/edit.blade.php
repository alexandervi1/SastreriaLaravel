@extends('admin.clientes.create')

@section('medidas')
@if(isset($cliente))
    @php
        $etiquetas = [
            'pecho' => 'Pecho', 'cintura' => 'Cintura', 'cadera' => 'Cadera', 'hombros' => 'Hombros',
            'espalda' => 'Espalda', 'largo_saco' => 'Largo saco', 'largo_manga' => 'Largo manga', 'cuello' => 'Cuello',
            'cintura_pantalon' => 'Cintura pantalón', 'cadera_pantalon' => 'Cadera pantalón', 'tiro' => 'Tiro',
            'muslo' => 'Muslo', 'entrepierna' => 'Entrepierna', 'largo_pantalon' => 'Largo pantalón', 'bota' => 'Bota',
            'estatura' => 'Estatura',
        ];
    @endphp
    <div class="card border-0 shadow-sm rounded-4 max-w-4xl mx-auto mt-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-ruler-combined text-primary me-2"></i> Medidas del cliente</h5>
            <span class="badge bg-light text-muted fw-normal">{{ $medidas->count() }} {{ \Illuminate\Support\Str::plural('perfil', $medidas->count()) }}</span>
        </div>
        <div class="card-body p-4">
            @if($medidas->isEmpty())
                <p class="text-muted mb-0">El cliente todavía no ha guardado perfiles de medidas.</p>
            @else
                @foreach($medidas as $medida)
                    <div class="border rounded-4 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $medida->nombre }}</strong>
                                @if($medida->tomadas_en_taller)
                                    <span class="badge bg-success rounded-pill ms-2">Tomadas en taller</span>
                                @endif
                            </div>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#medida-admin-{{ $medida->medida_id }}">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </div>
                        <div class="collapse mt-3" id="medida-admin-{{ $medida->medida_id }}">
                            <form action="{{ route('admin.clientes.medidas.update', [$cliente->usuario_id, $medida->medida_id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Nombre</label>
                                        <input class="form-control" name="nombre" value="{{ $medida->nombre }}" required maxlength="60">
                                    </div>
                                    @foreach($etiquetas as $campo => $etiqueta)
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small fw-bold">{{ $etiqueta }}</label>
                                            <input type="number" step="0.1" min="10" max="250" class="form-control" name="{{ $campo }}" value="{{ $medida->{$campo} }}">
                                        </div>
                                    @endforeach
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Notas</label>
                                        <textarea class="form-control" name="notas" rows="2">{{ $medida->notas }}</textarea>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center">
                                        <div class="form-check">
                                            <input type="hidden" name="tomadas_en_taller" value="0">
                                            <input class="form-check-input" type="checkbox" id="taller-{{ $medida->medida_id }}" name="tomadas_en_taller" value="1" {{ $medida->tomadas_en_taller ? 'checked' : '' }}>
                                            <label class="form-check-label" for="taller-{{ $medida->medida_id }}">Tomadas en el taller</label>
                                        </div>
                                    </div>
                                    <div class="col-md-8 text-end">
                                        <button type="submit" class="btn btn-primary">Guardar medidas</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endif
@endsection
