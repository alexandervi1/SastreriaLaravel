@extends('layouts.app')

@section('title', 'Quiénes Somos')

@section('content')
<div class="row align-items-center mb-5 my-5">
    <div class="col-md-6 order-md-2">
        <h2 class="display-5 fw-bold text-info mb-4">Comercial de Mariscos "Don Walter"</h2>
        <p class="lead">Somos una empresa familiar con más de 20 años de experiencia ofreciendo los productos del mar más frescos y de la más alta calidad.</p>
        <p>Trabajamos directamente con pescadores artesanales para asegurar que cada producto que llega a su mesa cumpla con nuestros estrictos estándares de frescura, responsabilidad ecológica y precio justo.</p>
        
        <div class="row mt-5 text-center g-4">
            <div class="col-6">
                <i class="fas fa-anchor fa-3x text-primary mb-3"></i>
                <h5 class="fw-bold">Calidad</h5>
                <p class="text-muted small">Seleccionamos a mano lo mejor del día.</p>
            </div>
            <div class="col-6">
                <i class="fas fa-truck-fast fa-3x text-primary mb-3"></i>
                <h5 class="fw-bold">Frescura</h5>
                <p class="text-muted small">Del mar al mostrador en horas.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 order-md-1 position-relative pe-md-5">
        <img src="{{ asset('img/placeholder.svg') }}" class="img-fluid rounded-4 shadow-lg w-100" alt="Imagen">
        <div class="position-absolute bottom-0 end-0 bg-white p-4 rounded-top-left-4 pe-md-5 shadow-sm d-none d-md-block" style="border-top-left-radius: 2rem;">
            <p class="fs-4 fw-bold text-dark mb-0 align-items-center d-flex">
                <i class="fas fa-star text-warning me-2"></i> Tradición Familiar
            </p>
        </div>
    </div>
</div>
@endsection
