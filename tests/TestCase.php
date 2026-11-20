<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // El memo estático de configuración vive en el proceso; se limpia por prueba.
        \App\Models\Configuracion::flush();
    }

    protected function activarVentaPorTalla(): void
    {
        \App\Models\Configuracion::updateOrCreate(
            ['clave' => 'venta_por_talla'],
            ['valor' => '1']
        );

        \App\Models\Configuracion::flush();
    }
}
