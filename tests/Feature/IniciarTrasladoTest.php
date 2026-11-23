<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\ComedorPopular;
use App\Models\CategoriaAlimento;
use App\Models\LoteAlimento;
use App\Models\SolicitudRescate;
use App\Models\Traslado;
use App\Services\IniciarTraslado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;
use Tests\TestCase;

class IniciarTrasladoTest extends TestCase
{
    use RefreshDatabase;

    private function crearEscenario(
        string $estadoTraslado = 'pendiente',
        bool $asignarColaborador = true
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

        // Crear responsable.
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

        // Crear colaborador.
        $colaborador = User::create([
            'name' => 'Colaborador de prueba',
            'email' => 'colaborador@test.com',
            'password' => 'password123',
            'role_id' => $rolColaborador->id,
            'phone' => '977777777',
            'is_active' => true,
        ]);

        // Asignar colaborador cuando corresponda.
        if ($asignarColaborador) {
            $traslado->colaboradores()->attach($colaborador->id);
        }

        return compact(
            'traslado',
            'solicitud',
            'colaborador'
        );
    }

    public function test_se_puede_iniciar_un_traslado_pendiente_con_colaboradores(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(IniciarTraslado::class);

        $traslado = $servicio->ejecutar(
            $datos['traslado']->id
        );

        $this->assertEquals('en_camino', $traslado->estado);

        $this->assertDatabaseHas('traslados', [
            'id' => $datos['traslado']->id,
            'estado' => 'en_camino',
        ]);
    }

    public function test_no_se_puede_iniciar_un_traslado_sin_colaboradores(): void
    {
        $datos = $this->crearEscenario(
            'pendiente',
            false
        );

        $servicio = app(IniciarTraslado::class);

        $this->expectException(RuntimeException::class);

        $servicio->ejecutar($datos['traslado']->id);
    }

    public function test_no_se_puede_iniciar_un_traslado_que_ya_esta_en_camino(): void
    {
        $datos = $this->crearEscenario('en_camino');

        $servicio = app(IniciarTraslado::class);

        $this->expectException(RuntimeException::class);

        $servicio->ejecutar($datos['traslado']->id);
    }

    public function test_no_se_puede_iniciar_un_traslado_entregado(): void
    {
        $datos = $this->crearEscenario('entregado');

        $servicio = app(IniciarTraslado::class);

        $this->expectException(RuntimeException::class);

        $servicio->ejecutar($datos['traslado']->id);
    }

    public function test_no_se_puede_iniciar_un_traslado_cancelado(): void
    {
        $datos = $this->crearEscenario('cancelado');

        $servicio = app(IniciarTraslado::class);

        $this->expectException(RuntimeException::class);

        $servicio->ejecutar($datos['traslado']->id);
    }

    public function test_no_se_puede_iniciar_un_traslado_dos_veces(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(IniciarTraslado::class);

        $servicio->ejecutar($datos['traslado']->id);

        $this->expectException(RuntimeException::class);

        $servicio->ejecutar($datos['traslado']->id);
    }

    public function test_no_se_puede_iniciar_un_traslado_inexistente(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(IniciarTraslado::class);

        $this->expectException(ModelNotFoundException::class);

        $servicio->ejecutar(99999);
    }

    public function test_devuelve_el_traslado_con_solicitud_y_colaboradores_cargados(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(IniciarTraslado::class);

        $traslado = $servicio->ejecutar(
            $datos['traslado']->id
        );

        $this->assertTrue($traslado->relationLoaded('solicitud'));

        $this->assertTrue($traslado->relationLoaded('colaboradores'));

        $this->assertEquals(
            $datos['solicitud']->id,
            $traslado->solicitud->id
        );

        $this->assertCount(1, $traslado->colaboradores);
    }
}
