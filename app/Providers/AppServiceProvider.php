<?php

namespace App\Providers;

use App\Http\View\Composers\AdminLayoutComposer;
use App\Models\Configuracion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Fuera de producción, un lazy load es un fallo visible en las pruebas.
        Model::preventLazyLoading(! app()->isProduction());

        $this->aplicarZonaHoraria();

        View::composer('layouts.admin', AdminLayoutComposer::class);

        // Se calcula en cada render para reflejar cambios de configuración sin reiniciar.
        View::composer('*', function ($view) {
            $view->with('negocio', $this->datosDelNegocio());
        });
    }

    /**
     * Aplica la zona horaria configurada. Si la tabla aún no existe
     * (instalación o migraciones) se conserva la de config/app.php.
     */
    private function aplicarZonaHoraria(): void
    {
        try {
            $zona = Configuracion::obtenerZonaHoraria();

            if (in_array($zona, timezone_identifiers_list(), true)) {
                date_default_timezone_set($zona);
                config(['app.timezone' => $zona]);
            }
        } catch (QueryException) {
            // Base de datos no disponible todavía.
        }
    }

    /**
     * @return array{nombre: string, ruc: string, direccion: string, telefono: string, leyenda: string, logo: string}
     */
    private function datosDelNegocio(): array
    {
        try {
            return [
                'nombre' => (string) Configuracion::obtenerValor('negocio_nombre', '<NOMBRE_NEGOCIO>'),
                'ruc' => (string) Configuracion::obtenerValor('negocio_ruc', ''),
                'direccion' => Configuracion::obtenerDireccionPublica(),
                'direccion_publica' => Configuracion::obtenerDireccionPublica(),
                'telefono' => (string) Configuracion::obtenerValor('negocio_telefono', ''),
                'leyenda' => (string) Configuracion::obtenerValor('negocio_leyenda', ''),
                'logo' => Configuracion::obtenerLogo(),
            ];
        } catch (QueryException) {
            return [
                'nombre' => '<NOMBRE_NEGOCIO>',
                'ruc' => '',
                'direccion' => '',
                'direccion_publica' => '',
                'telefono' => '',
                'leyenda' => '',
                'logo' => 'img/placeholder.svg',
            ];
        }
    }
}
