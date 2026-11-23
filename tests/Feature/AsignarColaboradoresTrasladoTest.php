<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\ComedorPopular;
use App\Models\CategoriaAlimento;
use App\Models\LoteAlimento;
use App\Models\SolicitudRescate;
use App\Models\Traslado;
use App\Services\AsignarColaboradoresTraslado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;
use Tests\TestCase;

class AsignarColaboradoresTrasladoTest extends TestCase
{
    use RefreshDatabase;

    private function crearEscenario(
        string $estadoTraslado = 'pendiente'
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

        $rolColaborador = Role::create([
            'name' => 'Colaborador',
            'slug' => 'colaborador',
        ]);

        // Crear donante.
        $donante = User::create([
            'name' => 'Donante de prueba',
            'email' => 'donante@test.com',
            'password' => 'password123',
            'role_id' => $rolDonante->id,
            'phone' => '999999999',
            'is_active' => true,
        ]);

        // Crear responsable del comedor.
        $responsable = User::create([
            'name' => 'Responsable de prueba',
            'email' => 'comedor@test.com',
            'password' => 'password123',
            'role_id' => $rolComedor->id,
            'phone' => '988888888',
            'is_active' => true,
        ]);

        // Crear comedor.
        $comedor = ComedorPopular::create([
            'user_id' => $responsable->id,
            'nombre' => 'Comedor de prueba',
            'direccion' => 'Av. La Cultura 100',
            'distrito' => 'San Sebastián',
            'raciones_diarias' => 80,
            'tiene_refrigeracion' => true,
        ]);

        // Crear categoría.
        $categoria = CategoriaAlimento::create([
            'nombre' => 'Frutas',
            'descripcion' => 'Frutas frescas',
        ]);

        // Crear lote.
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

        // Crear solicitud aceptada.
        $solicitud = SolicitudRescate::create([
            'lot_id' => $lote->id,
            'comedor_id' => $comedor->id,
            'cantidad_kg' => 5,
            'estado' => 'aceptada',
        ]);

        // Crear traslado.
        $traslado = Traslado::create([
            'solicitud_id' => $solicitud->id,
            'programado_at' => now()->addDay(),
            'estado' => $estadoTraslado,
            'notas' => 'Traslado de prueba',
        ]);

        // Crear colaboradores.
        $colaborador1 = User::create([
            'name' => 'Colaborador uno',
            'email' => 'colaborador1@test.com',
            'password' => 'password123',
            'role_id' => $rolColaborador->id,
            'phone' => '977777777',
            'is_active' => true,
        ]);

        $colaborador2 = User::create([
            'name' => 'Colaborador dos',
            'email' => 'colaborador2@test.com',
            'password' => 'password123',
            'role_id' => $rolColaborador->id,
            'phone' => '966666666',
            'is_active' => true,
        ]);

        return compact(
            'traslado',
            'colaborador1',
            'colaborador2',
            'donante',
            'responsable',
            'rolColaborador'
        );
    }

    public function test_se_puede_asignar_un_colaborador_activo(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(AsignarColaboradoresTraslado::class);

        $traslado = $servicio->ejecutar(
            $datos['traslado']->id,
            [$datos['colaborador1']->id]
        );

        $this->assertCount(1, $traslado->colaboradores);

        $this->assertDatabaseHas('traslado_user', [
            'traslado_id' => $datos['traslado']->id,
            'user_id' => $datos['colaborador1']->id,
        ]);
    }

    public function test_se_pueden_asignar_varios_colaboradores(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(AsignarColaboradoresTraslado::class);

        $traslado = $servicio->ejecutar(
            $datos['traslado']->id,
            [
                $datos['colaborador1']->id,
                $datos['colaborador2']->id,
            ]
        );

        $this->assertCount(2, $traslado->colaboradores);
    }

    public function test_no_se_puede_asignar_un_usuario_con_rol_donante(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(AsignarColaboradoresTraslado::class);

        $this->expectException(RuntimeException::class);

        $servicio->ejecutar(
            $datos['traslado']->id,
            [$datos['donante']->id]
        );
    }

    public function test_no_se_puede_asignar_un_usuario_con_rol_comedor(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(AsignarColaboradoresTraslado::class);

        $this->expectException(RuntimeException::class);

        $servicio->ejecutar(
            $datos['traslado']->id,
            [$datos['responsable']->id]
        );
    }

    public function test_no_se_puede_asignar_un_colaborador_inactivo(): void
    {
        $datos = $this->crearEscenario();

        $datos['colaborador1']->update([
            'is_active' => false,
        ]);

        $servicio = app(AsignarColaboradoresTraslado::class);

        $this->expectException(RuntimeException::class);

        $servicio->ejecutar(
            $datos['traslado']->id,
            [$datos['colaborador1']->id]
        );
    }

    public function test_no_se_puede_asignar_un_usuario_inexistente(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(AsignarColaboradoresTraslado::class);

        $this->expectException(RuntimeException::class);

        $servicio->ejecutar(
            $datos['traslado']->id,
            [99999]
        );
    }

    public function test_no_se_puede_asignar_una_lista_vacia(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(AsignarColaboradoresTraslado::class);

        $this->expectException(RuntimeException::class);

        $servicio->ejecutar(
            $datos['traslado']->id,
            []
        );
    }

    public function test_los_ids_duplicados_no_generan_asignaciones_duplicadas(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(AsignarColaboradoresTraslado::class);

        $traslado = $servicio->ejecutar(
            $datos['traslado']->id,
            [
                $datos['colaborador1']->id,
                $datos['colaborador1']->id,
            ]
        );

        $this->assertCount(1, $traslado->colaboradores);

        $this->assertDatabaseCount('traslado_user', 1);
    }

    public function test_se_conservan_los_colaboradores_asignados_anteriormente(): void
    {
        $datos = $this->crearEscenario();

        $datos['traslado']->colaboradores()->attach(
            $datos['colaborador1']->id
        );

        $servicio = app(AsignarColaboradoresTraslado::class);

        $traslado = $servicio->ejecutar(
            $datos['traslado']->id,
            [$datos['colaborador2']->id]
        );

        $this->assertCount(2, $traslado->colaboradores);

        $this->assertDatabaseHas('traslado_user', [
            'traslado_id' => $datos['traslado']->id,
            'user_id' => $datos['colaborador1']->id,
        ]);

        $this->assertDatabaseHas('traslado_user', [
            'traslado_id' => $datos['traslado']->id,
            'user_id' => $datos['colaborador2']->id,
        ]);
    }

    public function test_no_se_pueden_asignar_colaboradores_a_un_traslado_en_camino(): void
    {
        $datos = $this->crearEscenario('en_camino');

        $servicio = app(AsignarColaboradoresTraslado::class);

        $this->expectException(RuntimeException::class);

        $servicio->ejecutar(
            $datos['traslado']->id,
            [$datos['colaborador1']->id]
        );
    }

    public function test_no_se_pueden_asignar_colaboradores_a_un_traslado_entregado(): void
    {
        $datos = $this->crearEscenario('entregado');

        $servicio = app(AsignarColaboradoresTraslado::class);

        $this->expectException(RuntimeException::class);

        $servicio->ejecutar(
            $datos['traslado']->id,
            [$datos['colaborador1']->id]
        );
    }

    public function test_no_se_puede_asignar_a_un_traslado_inexistente(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(AsignarColaboradoresTraslado::class);

        $this->expectException(ModelNotFoundException::class);

        $servicio->ejecutar(
            99999,
            [$datos['colaborador1']->id]
        );
    }
}
