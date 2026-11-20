@extends('layouts.app')

@section('title', 'Políticas de cambios y devoluciones')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h1 class="display-6 fw-bold mb-4">Políticas de cambios y devoluciones</h1>
        <p class="text-muted">[REVISAR] Este texto es una propuesta y debe validarlo la dueña del negocio antes de publicar.</p>

        <div class="bg-white rounded-4 shadow-sm p-4 p-md-5">
            <h2 class="h5 fw-bold">Prendas a medida</h2>
            <p class="text-muted">Las prendas confeccionadas a medida no admiten devolución. Incluyen ajustes gratuitos dentro de los 15 días posteriores a la entrega, sobre las medidas y el corte acordados.</p>

            <h2 class="h5 fw-bold mt-4">Anticipo</h2>
            <p class="text-muted">Para comenzar la confección se pide un anticipo del [COMPLETAR %]. El saldo se coordina al entregar la prenda.</p>

            <h2 class="h5 fw-bold mt-4">Citas</h2>
            <p class="text-muted mb-0">Puedes cancelar o reprogramar una cita hasta 24 horas antes. Si no puedes asistir, avísanos por WhatsApp para liberar el horario. Las citas a domicilio dependen de la zona de visitas configurada.</p>
        </div>
    </div>
</div>
@endsection
