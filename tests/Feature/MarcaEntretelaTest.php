<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MarcaEntretelaTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_portada_es_de_entretela_y_no_deja_marcadores(): void
    {
        $this->seed();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Entretela');
        $response->assertSee('Tu noche,');
        $response->assertSee('a medida.');
        $response->assertSee('Reservar cita de medidas');
        $response->assertDontSee('<NOMBRE_NEGOCIO>', false);
    }

    public function test_las_vistas_no_conservan_marcadores_ni_marca_heredada(): void
    {
        foreach (File::allFiles(resource_path('views')) as $archivo) {
            $contenido = File::get($archivo->getPathname());

            $this->assertStringNotContainsString('<NOMBRE_NEGOCIO>', $contenido, $archivo->getRelativePathname());
            $this->assertDoesNotMatchRegularExpression('/pescader/i', $contenido, $archivo->getRelativePathname());
            $this->assertStringNotContainsString('Don Walter', $contenido, $archivo->getRelativePathname());
        }
    }

    public function test_los_archivos_de_marca_estan_publicados(): void
    {
        foreach ([
            'favicon.svg',
            'img/negocio/colibri-oro.svg',
            'img/negocio/colibri-icono-oro.svg',
            'img/negocio/colibri-tinta.svg',
            'img/negocio/sello-negro-oro.svg',
        ] as $archivo) {
            $this->assertFileExists(public_path($archivo));
        }

        $this->assertFileDoesNotExist(public_path('img/negocio/logo-placeholder.svg'));
    }

    public function test_el_panel_usa_el_logo_en_tinta(): void
    {
        $this->seed();
        $admin = \App\Models\Usuario::where('rol_id', 1)->firstOrFail();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('img/negocio/colibri-tinta.svg');
    }
}
