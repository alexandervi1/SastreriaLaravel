<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inicio') · {{ $negocio['nombre'] }}</title>
    <meta name="description" content="@yield('meta_description', $negocio['leyenda'] ?: 'Sastrería de gala a medida en Riobamba.')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $negocio['nombre'] }}">
    <meta property="og:title" content="@yield('og_title', $negocio['nombre'])">
    <meta property="og:description" content="@yield('meta_description', $negocio['leyenda'] ?: 'Sastrería de gala a medida en Riobamba.')">
    <meta property="og:image" content="@yield('og_image', asset($negocio['logo']))">
    <meta property="og:type" content="website">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    @stack('styles')
</head>
<body class="@yield('body_class', 'tienda')">
    
    @include('partials.navbar')

    <main class="container py-4 min-vh-100">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @stack('scripts')
</body>
</html>
