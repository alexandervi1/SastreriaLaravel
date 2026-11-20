@extends('layouts.app')

@section('title', 'Orden de trabajo #' . $pedido->pedido_id)

@push('styles')
<style>
    @media print {
        .no-print, nav, footer { display: none !important; }
        .hoja { page-break-after: always; border: 0 !important; }
        .hoja:last-child { page-break-after: auto; }
    }
    .hoja { border: 1px solid #dee2e6; border-radius: 1rem; padding: 2rem; margin-bottom: 2rem; }
    .medidas td, .medidas th { padding: .35rem .5rem; }
</style>
@endpush

@section('content')
@php
    $etiquetas = [
        'pecho' => 'Pecho', 'busto' => 'Busto', 'cintura' => 'Cintura', 'cadera' => 'Cadera', 'hombros' => 'Hombros',
        'espalda' => 'Espalda', 'talle_delantero' => 'Talle delantero', 'talle_espalda' => 'Talle espalda',
        'largo_saco' => 'Largo del saco', 'largo_manga' => 'Largo de manga', 'cuello' => 'Cuello',
        'cintura_pantalon' => 'Cintura pantalón', 'cadera_pantalon' => 'Cadera pantalón', 'tiro' => 'Tiro',
        'muslo' => 'Muslo', 'entrepierna' => 'Entrepierna', 'largo_pantalon' => 'Largo pantalón', 'largo_falda' => 'Largo de falda', 'bota' => 'Bota',
        'estatura' => 'Estatura',
    ];
@endphp

<div class="no-print d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.pedidos.show', $pedido->pedido_id) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Volver al pedido
    </a>
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fas fa-print me-2"></i> Imprimir orden de trabajo
    </button>
</div>

@forelse($pedido->lineas->where('tipo', 'medida') as $linea)
    <div class="hoja bg-white">
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
            <div>
                <h3 class="fw-bold mb-0">Orden de trabajo</h3>
                <div class="text-muted">Pedido {{ $pedido->numero_comprobante ?? $pedido->pedido_id }}</div>
            </div>
            <div class="text-end small">
                <div><strong>Cliente:</strong> {{ $pedido->usuario->nombre_completo }}</div>
                <div><strong>Teléfono:</strong> {{ $pedido->usuario->telefono ?? '—' }}</div>
                <div><strong>Entrega:</strong> {{ $pedido->fecha_entrega_estimada?->format('d/m/Y') ?? 'por confirmar' }}</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Modelo</div>
                    <div class="fs-5 fw-bold">{{ $linea->producto_nombre }}</div>
                    <div class="small mt-2"><strong>Tela:</strong> {{ $linea->tela_nombre ?? '—' }}</div>
                    <div class="small"><strong>Color:</strong> {{ $linea->color ?? '—' }}</div>
                    <div class="small"><strong>Patrón:</strong> {{ isset($linea->medidas['patron']) ? (\App\Models\MedidaCliente::PATRONES[$linea->medidas['patron']] ?? 'Caballero') : '—' }}</div>
                    <div class="small"><strong>Prenda:</strong> {{ $linea->producto?->tipo_prenda_label ?? '—' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Notas de confección</div>
                    <div class="small">{{ $linea->notas ?: 'Sin notas.' }}</div>
                    @if($linea->requiere_cita)
                        <div class="badge bg-warning text-dark mt-2">Tomar medidas en el taller</div>
                    @endif
                </div>
            </div>
        </div>

        <h6 class="fw-bold border-bottom pb-2 mb-2">Medidas (cm)</h6>
        @if(is_array($linea->medidas))
            @php($patron = $linea->medidas['patron'] ?? 'caballero')
            @php($camposPatron = \App\Models\MedidaCliente::CAMPOS_PATRON[$patron] ?? \App\Models\MedidaCliente::CAMPOS_PATRON['caballero'])
            <table class="table table-sm medidas mb-0">
                <tbody>
                    @foreach($etiquetas as $campo => $etiqueta)
                        @if(in_array($campo, $camposPatron, true) && isset($linea->medidas[$campo]) && $linea->medidas[$campo] !== null)
                            <tr>
                                <th class="text-muted fw-normal" style="width: 40%;">{{ $etiqueta }}</th>
                                <td class="fw-bold">{{ $linea->medidas[$campo] }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted mb-0">Sin medidas registradas: se toman en el taller.</p>
        @endif
    </div>
@empty
    <div class="alert alert-info">Este pedido no tiene líneas a medida.</div>
@endforelse
@endsection
