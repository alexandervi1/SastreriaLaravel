<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContenidoSoloMedidaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_la_home_explica_el_proceso_a_medida(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Eliges la prenda y la tela');
        $response->assertSee('Tomamos tus medidas');
        $response->assertSee('Prueba con hilvanes');
        $response->assertSee('Entrega');
        $response->assertDontSee('por talla');
    }

    public function test_la_ayuda_es_una_guia_de_medidas_por_patron(): void
    {
        $response = $this->get(route('ayuda'));

        $response->assertOk();
        $response->assertSee('Guía de medidas por patrón');
        $response->assertSee('Caballero');
        $response->assertSee('Dama');
        $response->assertSee('Niño');
        $response->assertSee('Cómo tomar cada medida');
        $response->assertDontSee('Guía de tallas');
    }

    public function test_las_politicas_son_solo_a_medida(): void
    {
        $response = $this->get(route('politicas'));

        $response->assertOk();
        $response->assertSee('Las prendas confeccionadas a medida no admiten devolución');
        $response->assertSee('15 días');
        $response->assertSee('[COMPLETAR %]');
        $response->assertDontSee('Ternos por talla');
    }

    public function test_los_terminos_separan_hechura_iva_y_anticipo(): void
    {
        $response = $this->get(route('terminos'));

        $response->assertOk();
        $response->assertSee('no incluyen IVA');
        $response->assertSee('[COMPLETAR %]');
    }

    public function test_quienes_somos_usa_marcadores_sin_nombre_real(): void
    {
        $response = $this->get(route('quienes.somos'));

        $response->assertOk();
        $response->assertSee('[COMPLETAR: aprendiz en la sastrería familiar, 30 años de oficio en sacos y abrigos, licenciada en Diseño de Modas]');
        $response->assertDontSee('Doña');
    }
}
