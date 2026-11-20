@extends('layouts.app')

@section('title', 'Quiénes somos')

@section('content')
<div class="row g-5 align-items-center">
    <div class="col-lg-6">
        <h1 class="display-5 fw-bold mb-3">El oficio detrás de la prenda</h1>
        <p class="lead text-muted">
            [COMPLETAR: aprendiz en la sastrería familiar, 30 años de oficio en sacos y abrigos, licenciada en Diseño de Modas]
        </p>
        <p class="text-muted">
            Una sola persona corta, cose y prueba cada pieza en su taller de Riobamba.
            La especialidad son los sacos, americanas, chaquetas y abrigos para caballero, dama y niño.
            Lo que no se ve —la entretela, el hilván, el ajuste— es lo que da forma.
        </p>
        <div class="row g-3 mt-4">
            <div class="col-sm-6">
                <div class="border rounded-4 p-3 h-100 bg-white">
                    <h2 class="h6 fw-bold">Confección a medida</h2>
                    <p class="small text-muted mb-0">Sacos, chaquetas, abrigos y ternos para toda la familia.</p>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="border rounded-4 p-3 h-100 bg-white">
                    <h2 class="h6 fw-bold">Trato directo</h2>
                    <p class="small text-muted mb-0">Hablas con quien corta y cose tu prenda.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <img src="{{ asset('img/placeholder.svg') }}" alt="Taller de sastrería" class="img-fluid rounded-4 shadow-sm w-100">
        <p class="small text-muted mt-2 mb-0">[COMPLETAR: foto real del taller y de una prenda terminada]</p>
    </div>
</div>
@endsection
