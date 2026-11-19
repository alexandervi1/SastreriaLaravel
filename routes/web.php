<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductoPublicoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\Admin\ProductoController as AdminProductoController;
use App\Http\Controllers\Admin\ClienteController as AdminClienteController;
use App\Http\Controllers\Admin\ReporteController as AdminReporteController;
use App\Http\Controllers\Admin\ConfiguracionController as AdminConfiguracionController;

// ── Rutas Públicas ──
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/quienes-somos', [HomeController::class, 'quienesSomos'])->name('quienes.somos');
Route::get('/ayuda', [HomeController::class, 'ayuda'])->name('ayuda');
Route::get('/politicas', [HomeController::class, 'politicas'])->name('politicas');
Route::get('/terminos', [HomeController::class, 'terminos'])->name('terminos');



Route::get('/productos/{producto:slug}', [ProductoPublicoController::class, 'show'])->name('productos.show');
Route::get('/categorias/{categoria}', [CategoriaController::class, 'show'])->name('categorias.show');

// ── Rutas de Autenticación ──
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login-ip');
Route::get('/registro', [AuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:registro');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── Rutas de Cliente (Requiere Login + Rol Cliente o predeterminado para usuarios validos) ──
Route::middleware(['auth', 'cliente'])->group(function () {
    // Carrito
    Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
    Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
    Route::put('/carrito/actualizar', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
    Route::delete('/carrito/eliminar', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');

    // Pedidos (Checkout y ver historial)
    Route::post('/pedido/procesar', [PedidoController::class, 'process'])->name('pedidos.procesar');
    Route::get('/mis-pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/mis-pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
    Route::get('/mis-pedidos/{pedido}/recibo', [PedidoController::class, 'receipt'])->name('pedidos.receipt');

    // Perfiles de medidas
    Route::get('/mis-medidas', [\App\Http\Controllers\MedidaClienteController::class, 'index'])->name('medidas.index');
    Route::get('/mis-medidas/nueva', [\App\Http\Controllers\MedidaClienteController::class, 'create'])->name('medidas.create');
    Route::post('/mis-medidas', [\App\Http\Controllers\MedidaClienteController::class, 'store'])->name('medidas.store');
    Route::get('/mis-medidas/{medida}/editar', [\App\Http\Controllers\MedidaClienteController::class, 'edit'])->name('medidas.edit');
    Route::put('/mis-medidas/{medida}', [\App\Http\Controllers\MedidaClienteController::class, 'update'])->name('medidas.update');
    Route::delete('/mis-medidas/{medida}', [\App\Http\Controllers\MedidaClienteController::class, 'destroy'])->name('medidas.destroy');

    // Citas en el taller
    Route::get('/citas', [\App\Http\Controllers\CitaClienteController::class, 'index'])->name('citas.index');
    Route::get('/citas/nueva', [\App\Http\Controllers\CitaClienteController::class, 'create'])->name('citas.create');
    Route::post('/citas', [\App\Http\Controllers\CitaClienteController::class, 'store'])->name('citas.store')->middleware('throttle:citas');
    Route::delete('/citas/{cita}', [\App\Http\Controllers\CitaClienteController::class, 'destroy'])->name('citas.destroy');
});

// ── Rutas de Administrador (Requiere Rol Admin) ──
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // CRUD de Productos (Resource completo, sin show: el detalle público está en /productos/{id})
    Route::resource('productos', AdminProductoController::class)->except(['show']);

    // Catálogo de telas para confección a medida
    Route::resource('telas', \App\Http\Controllers\Admin\TelaController::class)->except(['show']);

    // Tallas y colores de cada modelo
    Route::resource('productos.variantes', \App\Http\Controllers\Admin\VarianteController::class)
        ->parameters(['variantes' => 'variante'])
        ->only(['store', 'update', 'destroy']);

    // Galería de imágenes
    Route::post('/productos/{producto}/imagenes', [\App\Http\Controllers\Admin\ProductoImagenController::class, 'store'])->name('productos.imagenes.store');
    Route::put('/productos/{producto}/imagenes/{imagen}', [\App\Http\Controllers\Admin\ProductoImagenController::class, 'update'])->name('productos.imagenes.update');
    Route::put('/productos/{producto}/imagenes/{imagen}/mover/{direccion}', [\App\Http\Controllers\Admin\ProductoImagenController::class, 'mover'])->name('productos.imagenes.mover');
    Route::put('/productos/{producto}/imagenes/{imagen}/portada', [\App\Http\Controllers\Admin\ProductoImagenController::class, 'portada'])->name('productos.imagenes.portada');
    Route::delete('/productos/{producto}/imagenes/{imagen}', [\App\Http\Controllers\Admin\ProductoImagenController::class, 'destroy'])->name('productos.imagenes.destroy');

    // CRUD de Categorías
    Route::resource('categorias', \App\Http\Controllers\Admin\CategoriaController::class)->except(['create', 'show', 'edit']);

    // CRUD de Clientes
    Route::resource('clientes', AdminClienteController::class)->except(['show']);
    Route::put('/clientes/{cliente}/medidas/{medida}', [\App\Http\Controllers\Admin\MedidaClienteController::class, 'update'])
        ->name('clientes.medidas.update');

    // Agenda del taller
    Route::get('/citas', [\App\Http\Controllers\Admin\CitaController::class, 'index'])->name('citas.index');
    Route::get('/citas/nueva', [\App\Http\Controllers\Admin\CitaController::class, 'create'])->name('citas.create');
    Route::post('/citas', [\App\Http\Controllers\Admin\CitaController::class, 'store'])->name('citas.store');
    Route::put('/citas/{cita}', [\App\Http\Controllers\Admin\CitaController::class, 'update'])->name('citas.update');
    Route::post('/citas/bloqueos', [\App\Http\Controllers\Admin\CitaController::class, 'storeBloqueo'])->name('citas.bloqueos.store');
    Route::delete('/citas/bloqueos/{bloqueo}', [\App\Http\Controllers\Admin\CitaController::class, 'destroyBloqueo'])->name('citas.bloqueos.destroy');

    // Carritos activos (seguimiento comercial)
    Route::get('/carritos', [\App\Http\Controllers\Admin\CarritoController::class, 'index'])->name('carritos.index');

    // Administradores del sistema
    Route::resource('administradores', \App\Http\Controllers\Admin\AdministradorController::class)
        ->parameters(['administradores' => 'administrador'])
        ->except(['show']);

    // Gestión de Pedidos
    Route::resource('pedidos', \App\Http\Controllers\Admin\PedidoController::class)->only(['index', 'show', 'update']);
    Route::get('/pedidos/{pedido}/orden-trabajo', [\App\Http\Controllers\Admin\PedidoController::class, 'ordenTrabajo'])
        ->name('pedidos.orden-trabajo');

    // Configuración (IVA)
    Route::get('/configuracion', [AdminConfiguracionController::class, 'edit'])->name('configuracion.edit');
    Route::put('/configuracion', [AdminConfiguracionController::class, 'update'])->name('configuracion.update');
    Route::put('/configuracion/seguridad', [AdminConfiguracionController::class, 'updateSecurity'])->name('configuracion.seguridad');

    // Reportes
    Route::get('/reportes/ventas', [AdminReporteController::class, 'ventas'])->name('reportes.ventas');
    Route::get('/reportes/mas-vendidos', [AdminReporteController::class, 'masVendidos'])->name('reportes.mas-vendidos');
    Route::get('/reportes/bajo-stock', [AdminReporteController::class, 'bajoStock'])->name('reportes.bajo-stock');
    Route::get('/reportes/stock-categoria', [AdminReporteController::class, 'stockPorCategoria'])->name('reportes.stock-categoria');
});
