@extends('layouts.app')

@section('title', 'Quiénes somos')

@section('content')
<div class="row g-5 align-items-center">
    <div class="col-lg-6">
        <h1 class="display-5 fw-bold mb-3">Una sastrería de oficio</h1>
        <p class="lead text-muted">
            [COMPLETAR: historia de la sastre, años de oficio, foto del taller]
        </p>
        <p class="text-muted">
            Cada terno se corta y ajusta a la persona que lo va a usar. Trabajamos con telas seleccionadas y tiempos de confección claros, para que llegues a la ocasión con un traje que se sienta tuyo.
        </p>
        <div class="row g-3 mt-4">
            <div class="col-sm-6">
                <div class="border rounded-4 p-3 h-100 bg-white">
                    <h2 class="h6 fw-bold">Confección a medida</h2>
                    <p class="small text-muted mb-0">Tela, medidas y pruebas en el taller.</p>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="border rounded-4 p-3 h-100 bg-white">
                    <h2 class="h6 fw-bold">Asesoría personal</h2>
                    <p class="small text-muted mb-0">Te acompañamos en corte, color y ocasión.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <img src="{{ asset('img/placeholder.svg') }}" alt="Taller de sastrería" class="img-fluid rounded-4 shadow-sm w-100">
        <p class="small text-muted mt-2 mb-0">[COMPLETAR: foto real del taller]</p>
    </div>
</div>
@endsection
