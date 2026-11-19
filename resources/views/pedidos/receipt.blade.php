@extends('layouts.app')

@section('title', 'Recibo de Compra - ' . $pedido->numero_comprobante)

@section('content')
<div class="no-print text-center mb-4">
    <button onclick="window.print()" class="btn btn-primary btn-lg shadow-sm">
        <i class="fas fa-print me-2"></i> Imprimir Recibo
    </button>
</div>

<div class="receipt-container shadow">
    <div class="receipt-header row">
        <div class="col-8">
            <div class="business-name">{{ $negocio['nombre'] }}</div>
            <div class="mt-2 small">
                <strong>RUC:</strong> {{ $negocio['ruc'] }}<br>
                <strong>Dirección:</strong> {{ $negocio['direccion'] }}<br>
                <strong>Teléfono:</strong> {{ $negocio['telefono'] }}<br>
                <strong>Matriz:</strong> MANTA - ECUADOR
            </div>
        </div>
        <div class="col-4 text-center">
            <img src="{{ asset($negocio['logo']) }}" alt="Logo" class="receipt-logo">
        </div>
    </div>

    <div class="receipt-title">
        <h4 class="mb-1 fw-bold">NOTA DE VENTA</h4>
        <div class="text-danger fw-bold">№ {{ $pedido->numero_comprobante }}</div>
    </div>

    <div class="info-grid small">
        <div class="border p-3 rounded">
            <h6 class="fw-bold border-bottom pb-2 mb-2">DATOS DEL CLIENTE</h6>
            <strong>Nombre:</strong> {{ $pedido->usuario->nombre_completo }}<br>
            <strong>C.I./RUC:</strong> {{ $pedido->usuario->identificacion ?? 'S/N' }}<br>
            <strong>Dirección:</strong> {{ $pedido->usuario->direccion }}<br>
            <strong>Correo:</strong> {{ $pedido->usuario->correo }}
        </div>
        <div class="border p-3 rounded">
            <h6 class="fw-bold border-bottom pb-2 mb-2">DETALLES DE EMISIÓN</h6>
            <strong>Fecha:</strong> {{ $pedido->fecha_pedido->format('d/m/Y') }}<br>
            <strong>Hora:</strong> {{ $pedido->fecha_pedido->format('H:i:s') }}<br>
            <strong>Estado:</strong> {{ mb_strtoupper($pedido->estado) }}
        </div>
    </div>

    <div class="table-responsive">
    <table class="table table-bordered table-receipt small">
        <thead>
            <tr>
                <th style="width: 10%;">Cant.</th>
                <th>Descripción</th>
                <th style="width: 15%;" class="text-end">P. Unit</th>
                <th style="width: 15%;" class="text-end">V. Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->lineas as $linea)
            <tr>
                <td>{{ $linea->cantidad }}</td>
                <td>{{ $linea->producto_nombre }}@if($linea->talla) Talla {{ $linea->talla }}@endif @if($linea->color) · {{ $linea->color }}@endif</td>
                <td class="text-end">${{ number_format($linea->precio_unitario, 2) }}</td>
                <td class="text-end">${{ number_format($linea->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    <div class="row mt-4">
        <div class="col-12 col-sm-7">
            <div class="border p-2 rounded small bg-light" style="min-height: 100px;">
                <strong>Observaciones:</strong><br>
                {{ $negocio['leyenda'] }}<br>
                Gracias por preferir Pescadería Don Walter. "Del mar a tu mesa".
            </div>
        </div>
        <div class="col-12 col-sm-5">
            <table class="table table-sm table-borderless fw-bold small mb-0">
                <thead class="visually-hidden">
                    <tr>
                        <th>Concepto</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>SUBTOTAL:</td>
                        <td class="text-end">${{ number_format($pedido->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>IVA ({{ $pedido->iva_pct }}%):</td>
                        <td class="text-end">${{ number_format($pedido->iva, 2) }}</td>
                    </tr>
                    <tr class="border-top table-secondary">
                        <td class="fs-6">VALOR TOTAL:</td>
                        <td class="text-end fs-5">${{ number_format($pedido->total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5 row text-center small">
        <div class="col-6">
            <div class="border-top mx-4 pt-2">FIRMA AUTORIZADA</div>
        </div>
        <div class="col-6">
            <div class="border-top mx-4 pt-2">RECIBÍ CONFORME</div>
        </div>
    </div>
</div>
@endsection
