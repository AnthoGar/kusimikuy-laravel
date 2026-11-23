<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\ComedorPopular;
use App\Models\CategoriaAlimento;
use App\Models\LoteAlimento;
use App\Models\SolicitudRescate;
use App\Models\Traslado;
use App\Models\Entrega;
use App\Services\RegistrarEntrega;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;
use Throwable;

class RegistrarEntregaTest extends TestCase
{
    use RefreshDatabase;

    private function crearEscenario(
        string $estadoSolicitud = 'aceptada',
        string $estadoTraslado = 'en_camino'
    ): array {

        // Crear roles.
        $rolDonante = Role::create([
            'name' => 'Donante',
            'slug' => 'donante',
        ]);

        $rolComedor = Role::create([
            'name' => 'Comedor',
            'slug' => 'comedor',
        ]);

        $rolAdministrador = Role::create([
            'name' => 'Administrador',
            'slug' => 'administrador',
        ]);

        $rolColaborador = Role::create([
            'name' => 'Colaborador',
            'slug' => 'colaborador',
        ]);

        // Crear usuarios.
        $donante = User::create([
            'name' => 'Donante de prueba',
            'email' => 'donante@test.com',
            'password' => 'password123',
            'role_id' => $rolDonante->id,
            'phone' => '999999999',
            'is_active' => true,
        ]);

        $responsable = User::create([
            'name' => 'Responsable de prueba',
            'email' => 'comedor@test.com',
            'password' => 'password123',
            'role_id' => $rolComedor->id,
            'phone' => '988888888',
            'is_active' => true,
        ]);

        $administrador = User::create([
            'name' => 'Administrador de prueba',
            'email' => 'admin@test.com',
            'password' => 'password123',
            'role_id' => $rolAdministrador->id,
            'phone' => '977777777',
            'is_active' => true,
        ]);

        $colaborador = User::create([
            'name' => 'Colaborador de prueba',
            'email' => 'colaborador@test.com',
            'password' => 'password123',
            'role_id' => $rolColaborador->id,
            'phone' => '966666666',
            'is_active' => true,
        ]);

        $otroResponsable = User::create([
            'name' => 'Responsable de otro comedor',
            'email' => 'otro.comedor@test.com',
            'password' => 'password123',
            'role_id' => $rolComedor->id,
            'phone' => '955555555',
            'is_active' => true,
        ]);

        // Crear un usuario inactivo.
        $usuarioInactivo = User::create([
            'name' => 'Usuario inactivo',
            'email' => 'inactivo@test.com',
            'password' => 'password123',
            'role_id' => $rolComedor->id,
            'phone' => '944444444',
            'is_active' => false,
        ]);

        // Crear comedores.
        $comedor = ComedorPopular::create([
            'user_id' => $responsable->id,
            'nombre' => 'Comedor de prueba',
            'direccion' => 'Av. La Cultura 100',
            'distrito' => 'San Sebastián',
            'raciones_diarias' => 80,
            'tiene_refrigeracion' => true,
        ]);

        $otroComedor = ComedorPopular::create([
            'user_id' => $otroResponsable->id,
            'nombre' => 'Otro comedor de prueba',
            'direccion' => 'Av. La Cultura 200',
            'distrito' => 'San Sebastián',
            'raciones_diarias' => 60,
            'tiene_refrigeracion' => false,
        ]);

        // Crear categoría.
        $categoria = CategoriaAlimento::create([
            'nombre' => 'Frutas',
            'descripcion' => 'Frutas frescas',
        ]);

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

        // Crear solicitud de rescate.
        $solicitud = SolicitudRescate::create([
            'lot_id' => $lote->id,
            'comedor_id' => $comedor->id,
            'cantidad_kg' => 5,
            'estado' => $estadoSolicitud,
        ]);

        // Crear traslado asociado.
        $traslado = Traslado::create([
            'solicitud_id' => $solicitud->id,
            'programado_at' => now()->addDay(),
            'estado' => $estadoTraslado,
            'notas' => 'Traslado de prueba',
        ]);

        return compact(
            'donante',
            'responsable',
            'administrador',
            'colaborador',
            'otroResponsable',
            'usuarioInactivo',
            'comedor',
            'otroComedor',
            'lote',
            'solicitud',
            'traslado'
        );
    }

    /**
     * Comprueba que una operación sea rechazada
     * con la excepción esperada.
     */
    private function assertServicioRechaza(
        callable $accion,
        string $excepcionEsperada
    ): void {

        try {
            $accion();

            $this->fail(
                "Se esperaba una excepción de tipo {$excepcionEsperada}."
            );
        } catch (Throwable $e) {
            $this->assertInstanceOf($excepcionEsperada, $e);
        }
    }

    public function test_responsable_del_comedor_puede_registrar_una_entrega(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $entrega = $servicio->ejecutar(
            $datos['traslado']->id,
            $datos['responsable']->id,
            4.50,
            'bueno',
            5,
            'Los alimentos llegaron en buen estado.'
        );

        $this->assertInstanceOf(Entrega::class, $entrega);

        $this->assertDatabaseHas('entregas', [
            'id' => $entrega->id,
            'traslado_id' => $datos['traslado']->id,
            'received_by' => $datos['responsable']->id,
            'cantidad_recibida_kg' => 4.50,
            'estado_conservacion' => 'bueno',
            'calificacion_calidad' => 5,
        ]);

        $this->assertDatabaseHas('traslados', [
            'id' => $datos['traslado']->id,
            'estado' => 'entregado',
        ]);
    }

    public function test_administrador_puede_registrar_una_entrega(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $entrega = $servicio->ejecutar(
            $datos['traslado']->id,
            $datos['administrador']->id,
            5.00,
            'bueno'
        );

        $this->assertDatabaseHas('entregas', [
            'id' => $entrega->id,
            'received_by' => $datos['administrador']->id,
        ]);
    }

    public function test_donante_no_puede_registrar_una_entrega(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['donante']->id,
                4.50,
                'bueno'
            ),
            AuthorizationException::class
        );

        $this->assertDatabaseCount('entregas', 0);
    }

    public function test_colaborador_no_puede_registrar_una_entrega(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['colaborador']->id,
                4.50,
                'bueno'
            ),
            AuthorizationException::class
        );

        $this->assertDatabaseCount('entregas', 0);
    }

    public function test_usuario_inactivo_no_puede_registrar_una_entrega(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['usuarioInactivo']->id,
                4.50,
                'bueno'
            ),
            AuthorizationException::class
        );

        $this->assertDatabaseCount('entregas', 0);
    }

    public function test_responsable_de_otro_comedor_no_puede_registrar_la_entrega(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['otroResponsable']->id,
                4.50,
                'bueno'
            ),
            AuthorizationException::class
        );

        $this->assertDatabaseCount('entregas', 0);
    }

    public function test_no_se_puede_registrar_entrega_con_traslado_pendiente(): void
    {
        $datos = $this->crearEscenario(
            'aceptada',
            'pendiente'
        );

        $servicio = app(RegistrarEntrega::class);

        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['responsable']->id,
                4.50,
                'bueno'
            ),
            RuntimeException::class
        );

        $this->assertDatabaseCount('entregas', 0);
    }

    public function test_no_se_puede_registrar_entrega_con_traslado_entregado(): void
    {
        $datos = $this->crearEscenario(
            'aceptada',
            'entregado'
        );

        $servicio = app(RegistrarEntrega::class);

        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['responsable']->id,
                4.50,
                'bueno'
            ),
            RuntimeException::class
        );

        $this->assertDatabaseCount('entregas', 0);
    }

    public function test_no_se_puede_registrar_entrega_con_solicitud_no_aceptada(): void
    {
        $datos = $this->crearEscenario(
            'pendiente',
            'en_camino'
        );

        $servicio = app(RegistrarEntrega::class);

        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['responsable']->id,
                4.50,
                'bueno'
            ),
            RuntimeException::class
        );

        $this->assertDatabaseCount('entregas', 0);
    }

    public function test_se_puede_recibir_la_cantidad_total_solicitada(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $entrega = $servicio->ejecutar(
            $datos['traslado']->id,
            $datos['responsable']->id,
            5.00,
            'bueno'
        );

        $this->assertDatabaseHas('entregas', [
            'id' => $entrega->id,
            'cantidad_recibida_kg' => 5.00,
        ]);
    }

    public function test_no_se_puede_recibir_mas_cantidad_que_la_solicitada(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['responsable']->id,
                5.50,
                'bueno'
            ),
            InvalidArgumentException::class
        );

        $this->assertDatabaseCount('entregas', 0);

        $this->assertDatabaseHas('traslados', [
            'id' => $datos['traslado']->id,
            'estado' => 'en_camino',
        ]);
    }

    public function test_no_se_puede_recibir_cantidad_cero(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['responsable']->id,
                0,
                'bueno'
            ),
            InvalidArgumentException::class
        );

        $this->assertDatabaseCount('entregas', 0);
    }

    public function test_no_se_puede_recibir_cantidad_negativa(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['responsable']->id,
                -1.00,
                'bueno'
            ),
            InvalidArgumentException::class
        );

        $this->assertDatabaseCount('entregas', 0);
    }

    public function test_no_se_puede_registrar_calificacion_menor_a_uno(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['responsable']->id,
                4.50,
                'bueno',
                0
            ),
            InvalidArgumentException::class
        );

        $this->assertDatabaseCount('entregas', 0);
    }

    public function test_no_se_puede_registrar_calificacion_mayor_a_cinco(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['responsable']->id,
                4.50,
                'bueno',
                6
            ),
            InvalidArgumentException::class
        );

        $this->assertDatabaseCount('entregas', 0);
    }

    public function test_no_se_puede_registrar_estado_de_conservacion_invalido(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['responsable']->id,
                4.50,
                'excelente'
            ),
            InvalidArgumentException::class
        );

        $this->assertDatabaseCount('entregas', 0);
    }

    public function test_no_se_puede_registrar_cantidad_con_mas_de_dos_decimales(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['responsable']->id,
                4.567,
                'bueno'
            ),
            InvalidArgumentException::class
        );

        $this->assertDatabaseCount('entregas', 0);
    }

    public function test_no_se_puede_registrar_una_segunda_entrega(): void
    {
        $datos = $this->crearEscenario();

        // Registrar la primera entrega.
        $servicio = app(RegistrarEntrega::class);

        $primeraEntrega = $servicio->ejecutar(
            $datos['traslado']->id,
            $datos['responsable']->id,
            4.50,
            'bueno'
        );

        // Intentar registrar otra entrega para el mismo traslado.
        $this->assertServicioRechaza(
            fn () => $servicio->ejecutar(
                $datos['traslado']->id,
                $datos['responsable']->id,
                4.00,
                'bueno'
            ),
            RuntimeException::class
        );

        $this->assertDatabaseCount('entregas', 1);

        $this->assertDatabaseHas('entregas', [
            'id' => $primeraEntrega->id,
            'traslado_id' => $datos['traslado']->id,
        ]);
    }

    public function test_al_registrar_entrega_el_traslado_cambia_a_entregado(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $servicio->ejecutar(
            $datos['traslado']->id,
            $datos['responsable']->id,
            4.50,
            'bueno'
        );

        $this->assertDatabaseHas('traslados', [
            'id' => $datos['traslado']->id,
            'estado' => 'entregado',
        ]);
    }

    public function test_entrega_devuelve_correctamente_sus_relaciones(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(RegistrarEntrega::class);

        $entrega = $servicio->ejecutar(
            $datos['traslado']->id,
            $datos['responsable']->id,
            4.50,
            'bueno'
        );

        $this->assertTrue($entrega->relationLoaded('traslado'));
        $this->assertTrue($entrega->relationLoaded('receptor'));

        $this->assertNotNull($entrega->traslado);
        $this->assertNotNull($entrega->traslado->solicitud);
        $this->assertNotNull($entrega->traslado->solicitud->comedor);

        $this->assertEquals(
            $datos['responsable']->id,
            $entrega->receptor->id
        );
    }
}
