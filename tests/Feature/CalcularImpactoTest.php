<?php

namespace Tests\Feature;

use App\Models\CategoriaAlimento;
use App\Models\ComedorPopular;
use App\Models\Entrega;
use App\Models\LoteAlimento;
use App\Models\Role;
use App\Models\SolicitudRescate;
use App\Models\Traslado;
use App\Models\User;
use App\Services\CalcularImpacto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalcularImpactoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crea un escenario básico con un comedor,
     * una solicitud y un traslado.
     */
    private function crearEscenario(
        string $estadoSolicitud = 'aceptada',
        string $estadoTraslado = 'entregado'
    ): array {

        // Obtener o crear el rol de comedor.
        $rolComedor = Role::firstOrCreate(
            ['slug' => 'comedor'],
            ['name' => 'Comedor']
        );

        // Obtener o crear el rol de donante.
        $rolDonante = Role::firstOrCreate(
            ['slug' => 'donante'],
            ['name' => 'Donante']
        );

        // Crear usuario responsable del comedor.
        $responsable = User::create([
            'name' => 'Responsable de prueba',
            'email' => 'comedor' . uniqid() . '@test.com',
            'password' => 'password123',
            'role_id' => $rolComedor->id,
            'phone' => '988888888',
            'is_active' => true,
        ]);

        // Crear comedor asociado al responsable.
        $comedor = ComedorPopular::create([
            'user_id' => $responsable->id,
            'nombre' => 'Comedor de prueba ' . uniqid(),
            'direccion' => 'Av. La Cultura 100',
            'distrito' => 'San Sebastián',
            'raciones_diarias' => 80,
            'tiene_refrigeracion' => true,
        ]);

        // Crear usuario donante.
        $donante = User::create([
            'name' => 'Donante de prueba',
            'email' => 'donante' . uniqid() . '@test.com',
            'password' => 'password123',
            'role_id' => $rolDonante->id,
            'phone' => '999999999',
            'is_active' => true,
        ]);

        // Crear categoría de alimento.
        $categoria = CategoriaAlimento::firstOrCreate(
            ['nombre' => 'Frutas'],
            ['descripcion' => 'Frutas frescas']
        );

        // Crear lote de alimentos.
        $lote = LoteAlimento::create([
            'donor_id' => $donante->id,
            'category_id' => $categoria->id,
            'nombre' => 'Lote de manzanas',
            'descripcion' => 'Manzanas frescas',
            'cantidad_kg' => 30,
            'disponible_kg' => 25,
            'estado_conservacion' => 'Bueno',
            'expiracion' => now()->addDays(5),
            'recojo_maximo' => now()->addDays(2),
            'estado' => 'disponible',
        ]);

        // Crear solicitud.
        $solicitud = SolicitudRescate::create([
            'lot_id' => $lote->id,
            'comedor_id' => $comedor->id,
            'cantidad_kg' => 5,
            'estado' => $estadoSolicitud,
        ]);

        // Crear traslado.
        $traslado = Traslado::create([
            'solicitud_id' => $solicitud->id,
            'programado_at' => now()->addDay(),
            'estado' => $estadoTraslado,
            'notas' => 'Traslado de prueba',
        ]);

        return compact(
            'comedor',
            'responsable',
            'solicitud',
            'traslado'
        );
    }

    /**
     * Registra una entrega directamente para probar
     * el cálculo de los indicadores de impacto.
     */
    private function crearEntrega(
        int $trasladoId,
        float $cantidadKg
    ): Entrega {

        // Obtener al responsable del comedor asociado al traslado.
        $traslado = Traslado::with('solicitud.comedor')
            ->findOrFail($trasladoId);

        $responsableId = $traslado->solicitud->comedor->user_id;

        return Entrega::create([
            'traslado_id' => $trasladoId,
            'received_by' => $responsableId,
            'recibido_at' => now(),
            'cantidad_recibida_kg' => $cantidadKg,
            'estado_conservacion' => 'bueno',
            'calificacion_calidad' => 5,
            'comentarios' => 'Entrega de prueba',
        ]);
    }

    public function test_sin_entregas_todos_los_indicadores_son_cero(): void
    {
        $servicio = app(CalcularImpacto::class);

        $resultado = $servicio->ejecutar();

        $this->assertSame([
            'kg_rescatados' => 0.0,
            'entregas_completadas' => 0,
            'comedores_beneficiados' => 0,
            'solicitudes_atendidas' => 0,
        ], $resultado);
    }

    public function test_calcula_correctamente_los_indicadores_de_una_entrega(): void
    {
        $datos = $this->crearEscenario();

        $this->crearEntrega(
            $datos['traslado']->id,
            4.50
        );

        $resultado = app(CalcularImpacto::class)->ejecutar();

        $this->assertSame([
            'kg_rescatados' => 4.5,
            'entregas_completadas' => 1,
            'comedores_beneficiados' => 1,
            'solicitudes_atendidas' => 1,
        ], $resultado);
    }

    public function test_suma_los_kilogramos_de_varias_entregas(): void
    {
        $primerEscenario = $this->crearEscenario();

        $this->crearEntrega(
            $primerEscenario['traslado']->id,
            4.50
        );

        $segundoEscenario = $this->crearEscenario();

        $this->crearEntrega(
            $segundoEscenario['traslado']->id,
            3.25
        );

        $resultado = app(CalcularImpacto::class)->ejecutar();

        $this->assertSame(7.75, $resultado['kg_rescatados']);
        $this->assertSame(2, $resultado['entregas_completadas']);
        $this->assertSame(2, $resultado['comedores_beneficiados']);
        $this->assertSame(2, $resultado['solicitudes_atendidas']);
    }

    public function test_cuenta_un_comedor_una_sola_vez_si_recibe_varias_entregas(): void
    {
        $primerEscenario = $this->crearEscenario();

        $this->crearEntrega(
            $primerEscenario['traslado']->id,
            4.50
        );

        // Crear una segunda solicitud para el mismo comedor.
        $segundaSolicitud = SolicitudRescate::create([
            'lot_id' => $primerEscenario['solicitud']->lot_id,
            'comedor_id' => $primerEscenario['comedor']->id,
            'cantidad_kg' => 3,
            'estado' => 'aceptada',
        ]);

        $segundoTraslado = Traslado::create([
            'solicitud_id' => $segundaSolicitud->id,
            'programado_at' => now()->addDay(),
            'estado' => 'entregado',
            'notas' => 'Segundo traslado',
        ]);

        $this->crearEntrega(
            $segundoTraslado->id,
            2.50
        );

        $resultado = app(CalcularImpacto::class)->ejecutar();

        $this->assertSame(7.0, $resultado['kg_rescatados']);
        $this->assertSame(2, $resultado['entregas_completadas']);
        $this->assertSame(1, $resultado['comedores_beneficiados']);
        $this->assertSame(2, $resultado['solicitudes_atendidas']);
    }

    public function test_no_cuenta_entregas_de_traslados_pendientes(): void
    {
        $datos = $this->crearEscenario(
            'aceptada',
            'pendiente'
        );

        $this->crearEntrega(
            $datos['traslado']->id,
            4.50
        );

        $resultado = app(CalcularImpacto::class)->ejecutar();

        $this->assertSame(0.0, $resultado['kg_rescatados']);
        $this->assertSame(0, $resultado['entregas_completadas']);
        $this->assertSame(0, $resultado['comedores_beneficiados']);
        $this->assertSame(0, $resultado['solicitudes_atendidas']);
    }

    public function test_no_cuenta_entregas_de_traslados_en_camino(): void
    {
        $datos = $this->crearEscenario(
            'aceptada',
            'en_camino'
        );

        $this->crearEntrega(
            $datos['traslado']->id,
            4.50
        );

        $resultado = app(CalcularImpacto::class)->ejecutar();

        $this->assertSame(0.0, $resultado['kg_rescatados']);
        $this->assertSame(0, $resultado['entregas_completadas']);
        $this->assertSame(0, $resultado['comedores_beneficiados']);
        $this->assertSame(0, $resultado['solicitudes_atendidas']);
    }

    public function test_calcula_el_impacto_con_la_cantidad_realmente_recibida(): void
    {
        $datos = $this->crearEscenario();

        // La solicitud fue de 5 kg, pero se recibieron 3.75 kg.
        $this->crearEntrega(
            $datos['traslado']->id,
            3.75
        );

        $resultado = app(CalcularImpacto::class)->ejecutar();

        $this->assertSame(3.75, $resultado['kg_rescatados']);
    }

    public function test_redondea_los_kilogramos_a_dos_decimales(): void
    {
        $datos = $this->crearEscenario();

        $this->crearEntrega(
            $datos['traslado']->id,
            4.567
        );

        $resultado = app(CalcularImpacto::class)->ejecutar();

        $this->assertSame(4.57, $resultado['kg_rescatados']);
    }
}
