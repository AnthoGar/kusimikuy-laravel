<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\ComedorPopular;
use App\Models\CategoriaAlimento;
use App\Models\LoteAlimento;
use App\Models\SolicitudRescate;
use App\Services\CrearTraslado;
use App\Models\Traslado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use RuntimeException;

class CrearTrasladoTest extends TestCase
{
    use RefreshDatabase;

    private function crearEscenario(string $estadoSolicitud = 'aceptada'): array
    {
        $rolDonante = Role::create([
            'name' => 'Donante',
            'slug' => 'donante',
        ]);

        $rolComedor = Role::create([
            'name' => 'Comedor',
            'slug' => 'comedor',
        ]);

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

        $comedor = ComedorPopular::create([
            'user_id' => $responsable->id,
            'nombre' => 'Comedor de prueba',
            'direccion' => 'Av. La Cultura 100',
            'distrito' => 'San Sebastián',
            'raciones_diarias' => 80,
            'tiene_refrigeracion' => true,
        ]);

        $categoria = CategoriaAlimento::create([
            'nombre' => 'Frutas',
            'descripcion' => 'Frutas frescas',
        ]);

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

        $solicitud = SolicitudRescate::create([
            'lot_id' => $lote->id,
            'comedor_id' => $comedor->id,
            'cantidad_kg' => 5,
            'estado' => $estadoSolicitud,
        ]);

        return compact('solicitud', 'lote');
    }

    public function test_se_puede_crear_traslado_para_solicitud_aceptada(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(CrearTraslado::class);

        $traslado = $servicio->ejecutar(
            $datos['solicitud']->id
        );

        $this->assertInstanceOf(Traslado::class, $traslado);

        $this->assertDatabaseHas('traslados', [
            'id' => $traslado->id,
            'solicitud_id' => $datos['solicitud']->id,
            'estado' => 'pendiente',
        ]);
    }

    public function test_no_se_puede_crear_traslado_para_solicitud_pendiente(): void
    {
        $datos = $this->crearEscenario('pendiente');

        $servicio = app(CrearTraslado::class);

        try {
            $servicio->ejecutar($datos['solicitud']->id);

            $this->fail('Se esperaba un rechazo para una solicitud pendiente.');
        } catch (RuntimeException $e) {
            $this->assertDatabaseMissing('traslados', [
                'solicitud_id' => $datos['solicitud']->id,
            ]);
        }
    }

    public function test_no_se_puede_crear_traslado_para_solicitud_rechazada(): void
    {
        $datos = $this->crearEscenario('rechazada');

        $servicio = app(CrearTraslado::class);

        try {
            $servicio->ejecutar($datos['solicitud']->id);

            $this->fail('Se esperaba un rechazo para una solicitud rechazada.');
        } catch (RuntimeException $e) {
            $this->assertDatabaseMissing('traslados', [
                'solicitud_id' => $datos['solicitud']->id,
            ]);
        }
    }

    public function test_no_se_puede_crear_dos_traslados_para_la_misma_solicitud(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(CrearTraslado::class);

        $primerTraslado = $servicio->ejecutar(
            $datos['solicitud']->id
        );

        try {
            $servicio->ejecutar($datos['solicitud']->id);

            $this->fail('No se debería permitir un segundo traslado.');
        } catch (RuntimeException $e) {
            $this->assertDatabaseCount('traslados', 1);

            $this->assertDatabaseHas('traslados', [
                'id' => $primerTraslado->id,
                'solicitud_id' => $datos['solicitud']->id,
                'estado' => 'pendiente',
            ]);
        }
    }

    public function test_traslado_se_crea_con_fecha_y_notas(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(CrearTraslado::class);

        $fecha = now()->addDay()->format('Y-m-d H:i:s');
        $notas = 'Coordinar recojo con el comedor.';

        $traslado = $servicio->ejecutar(
            $datos['solicitud']->id,
            $fecha,
            $notas
        );

        $this->assertDatabaseHas('traslados', [
            'id' => $traslado->id,
            'programado_at' => $fecha,
            'notas' => $notas,
            'estado' => 'pendiente',
        ]);
    }
}
