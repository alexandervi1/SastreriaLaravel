@extends('layouts.app')

@section('title', 'Términos y condiciones')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h1 class="display-6 fw-bold mb-4">Términos y condiciones</h1>
        <p class="text-muted">[REVISAR] Este texto es una propuesta y debe validarlo la dueña del negocio antes de publicar.</p>

        <div class="bg-white rounded-4 shadow-sm p-4 p-md-5">
            <h2 class="h5 fw-bold">Pedidos</h2>
            <p class="text-muted">El pedido queda registrado al confirmarlo desde el carrito. El pago se coordina por WhatsApp o transferencia; el pedido se considera confirmado cuando el taller lo valida.</p>

            <h2 class="h5 fw-bold mt-4">Precios e impuestos</h2>
            <p class="text-muted">Los precios se muestran en dólares e incluyen el IVA configurado al momento de la compra. El valor queda congelado en el pedido.</p>

            <h2 class="h5 fw-bold mt-4">Confección a medida</h2>
            <p class="text-muted">Las medidas se toman en el taller o se cargan desde un perfil del cliente. El tiempo de entrega estimado se calcula en días hábiles y puede variar por disponibilidad de tela.</p>

            <h2 class="h5 fw-bold mt-4">Datos personales</h2>
            <p class="text-muted mb-0">Usamos los datos de contacto y las medidas únicamente para gestionar pedidos y citas. No compartimos esa información con terceros.</p>
        </div>
    </div>
</div>
@endsection
