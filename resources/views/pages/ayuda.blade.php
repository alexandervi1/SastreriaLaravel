@extends('layouts.app')

@section('title', 'Ayuda y guía de medidas')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <p class="eyebrow mb-2">Antes del encargo</p>
        <h1 class="display-6 fw-bold mb-2">Guía de medidas por patrón</h1>
        <p class="lead text-muted mb-5">Cada prenda se corta con tu patrón. Estas son las medidas que necesitamos y cómo tomarlas.</p>

        <section class="mb-5" id="minimas">
            <h2 class="h4 fw-bold mb-3">Mínimos para confeccionar</h2>
            <div class="table-responsive bg-white rounded-4 shadow-sm">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th class="ps-4">Patrón</th><th class="pe-4">Medidas mínimas</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4 fw-bold">Caballero</td>
                            <td class="pe-4">Pecho, cintura, hombros, largo de manga y largo de saco. Para terno o pantalón, además entrepierna y largo de pantalón.</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">Dama</td>
                            <td class="pe-4">Busto, cintura, cadera, hombros, largo de manga, talle delantero, talle espalda y largo de saco. Para pantalón, largo de pantalón; para falda, largo de falda.</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">Niño</td>
                            <td class="pe-4">Pecho, cintura, largo de manga y largo de saco. Para terno o pantalón, además largo de pantalón.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="small text-muted mt-2 mb-0">Solo saco, chaqueta, abrigo o chaleco: no hacen falta medidas de pantalón ni de falda.</p>
        </section>

        <section class="mb-5" id="tomar-medidas">
            <h2 class="h4 fw-bold mb-3">Cómo tomar cada medida</h2>
            <p class="text-muted">Usa una cinta métrica blanda, sin apretar, sobre ropa interior o una camiseta fina. Pide ayuda para las medidas de espalda.</p>

            <h3 class="h6 fw-bold text-gold mt-4 mb-3">Torso</h3>
            <div class="row g-3">
                @foreach([
                    ['Pecho', 'Rodea la parte más ancha del pecho, con la cinta horizontal.'],
                    ['Busto', 'Rodea la parte más ancha del busto, sin comprimir.'],
                    ['Cintura', 'Mide sobre la cintura natural, sin apretar.'],
                    ['Cadera', 'Rodea la parte más ancha de la cadera con los pies juntos.'],
                    ['Hombros', 'De hombro a hombro, por la espalda, siguiendo la línea del saco.'],
                    ['Espalda', 'Desde la base del cuello hasta la cintura, por la espalda.'],
                    ['Talle delantero', 'Desde el hombro hasta la cintura, por delante.'],
                    ['Talle espalda', 'Desde el hombro hasta la cintura, por detrás.'],
                    ['Largo del saco', 'Desde la base del cuello hasta el largo deseado del saco.'],
                    ['Largo de manga', 'Desde el hombro hasta la muñeca, con el brazo relajado.'],
                    ['Cuello', 'Rodea la base del cuello con un dedo de holgura.'],
                ] as $medida)
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-white">
                            <h4 class="h6 fw-bold mb-1">{{ $medida[0] }}</h4>
                            <p class="small text-muted mb-0">{{ $medida[1] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <h3 class="h6 fw-bold text-gold mt-4 mb-3">Pantalón</h3>
            <div class="row g-3">
                @foreach([
                    ['Cintura de pantalón', 'Mide donde apoyarás el pantalón, con la cinta horizontal.'],
                    ['Cadera de pantalón', 'Rodea la parte más ancha, a la altura del cierre.'],
                    ['Tiro', 'Desde la cintura delantera hasta la cintura trasera, pasando por la entrepierna.'],
                    ['Muslo', 'Rodea la parte más ancha del muslo.'],
                    ['Entrepierna', 'Desde la entrepierna hasta el tobillo, de pie y sin zapatos.'],
                    ['Largo de pantalón', 'Desde la cintura hasta el borde del zapato.'],
                    ['Bota', 'Ancho deseado en el borde del pantalón.'],
                ] as $medida)
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-white">
                            <h4 class="h6 fw-bold mb-1">{{ $medida[0] }}</h4>
                            <p class="small text-muted mb-0">{{ $medida[1] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <h3 class="h6 fw-bold text-gold mt-4 mb-3">Falda y estatura</h3>
            <div class="row g-3">
                @foreach([
                    ['Largo de falda', 'Desde la cintura natural hasta el largo deseado, con zapatos.'],
                    ['Estatura', 'De pie, sin zapatos, desde la planta del pie hasta la coronilla.'],
                ] as $medida)
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100 bg-white">
                            <h4 class="h6 fw-bold mb-1">{{ $medida[0] }}</h4>
                            <p class="small text-muted mb-0">{{ $medida[1] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="small text-muted mt-3">
                También puedes <a href="{{ route('citas.create', ['tipo' => 'medidas']) }}">agendar una toma de medidas</a>
                en el taller o a domicilio dentro de la zona de visitas.
            </p>
        </section>

        <section class="mb-5" id="preguntas">
            <h2 class="h4 fw-bold mb-3">Preguntas frecuentes</h2>
            <div class="accordion" id="faq">
                @foreach([
                    ['¿Cómo pago mi encargo?', 'Coordinamos el pago por WhatsApp o transferencia. Para comenzar la confección se pide un anticipo del [COMPLETAR %]; el saldo se paga al entregar.'],
                    ['¿Puedo devolver una prenda a medida?', 'Las prendas a medida no admiten devolución porque se cortan para ti. Incluyen ajustes gratuitos dentro de los 15 días posteriores a la entrega.'],
                    ['¿Cuánto tarda una prenda?', 'La fecha estimada se muestra en la ficha y al confirmar el pedido. Depende de los días de confección y de la cola del taller.'],
                    ['¿Puedo cancelar una cita?', 'Sí, hasta 24 horas antes desde «Mis citas». Después de ese plazo, avísanos por WhatsApp.'],
                    ['¿Puedo tomar mis medidas en casa?', 'Sí, si estás dentro de la zona de visitas. Elige «A domicilio» al reservar y deja tu dirección.'],
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

        @php($wa = \App\Support\WhatsApp::enlace('Hola, necesito ayuda con un encargo a medida.'))
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
