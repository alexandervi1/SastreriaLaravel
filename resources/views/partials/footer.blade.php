<footer class="site-footer pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <p class="brand-wordmark fs-5 mb-2">{{ $negocio['nombre'] }}</p>
                <p class="small mb-2 text-gold">{{ $negocio['leyenda'] ?: 'Lo que no se ve es lo que da forma.' }}</p>
                <p class="small mb-1 footer-meta"><i class="fas fa-map-marker-alt me-2"></i>{{ $negocio['direccion'] ?: 'Dirección pendiente de configurar' }}</p>
                <p class="small mb-0 footer-meta"><i class="fas fa-phone me-2"></i>{{ $negocio['telefono'] ?: 'Teléfono pendiente de configurar' }}</p>
            </div>
            <div class="col-md-4">
                <p class="fw-bold mb-2">Información</p>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-1"><a href="{{ route('ayuda') }}" class="footer-link">Ayuda y guía de medidas</a></li>
                    <li class="mb-1"><a href="{{ route('politicas') }}" class="footer-link">Cambios y devoluciones</a></li>
                    <li><a href="{{ route('terminos') }}" class="footer-link">Términos y condiciones</a></li>
                </ul>
            </div>
            <div class="col-md-3 text-md-end">
                @php($waFooter = \App\Support\WhatsApp::enlace('Hola, quiero información sobre un terno.'))
                @if($waFooter)
                    <a href="{{ $waFooter }}" target="_blank" rel="noopener" class="btn btn-success btn-sm rounded-pill">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp
                    </a>
                @endif
            </div>
        </div>
        <hr>
        <p class="small mb-0 text-center">&copy; {{ date('Y') }} {{ $negocio['nombre'] }}. Todos los derechos reservados.</p>
    </div>
</footer>
