@extends('layouts.app')

@section('title', 'Ayuda')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <h1 class="display-6 fw-bold mb-2">Ayuda</h1>
        <p class="lead text-muted mb-5">Guías de tallas, medidas y respuestas rápidas antes de comprar.</p>

        <section class="mb-5" id="tallas">
            <h2 class="h4 fw-bold mb-3">Guía de tallas</h2>
            <div class="table-responsive bg-white rounded-4 shadow-sm">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th class="ps-4">Talla</th><th>Pecho (cm)</th><th>Cintura (cm)</th><th class="pe-4">Cadera (cm)</th></tr>
                    </thead>
                    <tbody>
                        @foreach([['36','86–89','72–75','90–93'],['38','90–93','76–79','94–97'],['40','94–97','80–83','98–101'],['42','98–101','84–87','102–105'],['44','102–105','88–91','106–109'],['46','106–109','92–95','110–113'],['48','110–113','96–99','114–117']] as $fila)
                            <tr><td class="ps-4 fw-bold">{{ $fila[0] }}</td><td>{{ $fila[1] }}</td><td>{{ $fila[2] }}</td><td class="pe-4">{{ $fila[3] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="small text-muted mt-2">Si estás entre dos tallas, elige la mayor o pide una cita para tomar medidas.</p>
        </section>

        <section class="mb-5" id="tomar-medidas">
            <h2 class="h4 fw-bold mb-3">Cómo tomar tus medidas</h2>
            <div class="row g-4">
                @foreach([
                    ['Pecho', 'Rodea la parte más ancha del pecho, con la cinta horizontal y sin apretar.'],
                    ['Cintura', 'Mide sobre la cintura natural, a la altura del ombligo.'],
                    ['Cadera', 'Rodea la parte más ancha de la cadera, con los pies juntos.'],
                    ['Hombros', 'De hombro a hombro, por la espalda, siguiendo la línea del traje.'],
                    ['Largo de manga', 'Desde el hombro hasta la muñeca, con el brazo relajado.'],
                    ['Entrepierna', 'Desde la entrepierna hasta el tobillo, de pie y sin zapatos.'],
                    ['Largo de pantalón', 'Desde la cintura hasta el borde del zapato.'],
                ] as $medida)
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-white">
                            <h3 class="h6 fw-bold mb-1">{{ $medida[0] }}</h3>
                            <p class="small text-muted mb-0">{{ $medida[1] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="small text-muted mt-3">También puedes <a href="{{ route('citas.create', ['tipo' => 'medidas']) }}">agendar una toma de medidas en el taller</a>.</p>
        </section>

        <section class="mb-5" id="preguntas">
            <h2 class="h4 fw-bold mb-3">Preguntas frecuentes</h2>
            <div class="accordion" id="faq">
                @foreach([
                    ['¿Cómo pago mi pedido?', 'Al confirmar el pedido coordinamos el pago por WhatsApp o transferencia. No hay pasarela de pago en línea.'],
                    ['¿Puedo cambiar un terno por talla?', 'Sí, dentro de los 7 días con la etiqueta puesta. Los ternos a medida no admiten devolución, pero incluyen ajustes gratuitos dentro de 15 días.'],
                    ['¿Cuánto tarda un terno a medida?', 'Depende del modelo: los días de confección se muestran en la ficha antes de encargar.'],
                    ['¿Puedo cancelar una cita?', 'Sí, hasta 24 horas antes desde “Mis citas”. Después de ese plazo, llama al taller.'],
                ] as $i => $faq)
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }} fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $i }}">
                                {{ $faq[0] }}
                            </button>
                        </h3>
                        <div id="faq-{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#faq">
                            <div class="accordion-body text-muted">{{ $faq[1] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        @php($wa = \App\Support\WhatsApp::enlace('Hola, necesito ayuda con un pedido.'))
        @if($wa)
            <div class="text-center">
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn btn-success btn-lg rounded-pill px-4">
                    <i class="fab fa-whatsapp me-2"></i> Escribir al taller
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
