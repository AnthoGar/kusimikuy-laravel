<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\ComedorPopular;
use App\Models\CategoriaAlimento;
use App\Models\LoteAlimento;
use App\Models\SolicitudRescate;
use App\Services\AceptarSolicitudRescate;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class AceptarSolicitudRescateTest extends TestCase
{
    use RefreshDatabase;

    private function crearEscenario(): array
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

        $otroDonante = User::create([
            'name' => 'Otro donante',
            'email' => 'otro@test.com',
            'password' => 'password123',
            'role_id' => $rolDonante->id,
            'phone' => '977777777',
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
            'disponible_kg' => 30,
            'estado_conservacion' => 'Bueno',
            'expiracion' => now()->addDays(5),
            'recojo_maximo' => now()->addDays(2),
            'estado' => 'disponible',
        ]);

        $solicitud = SolicitudRescate::create([
            'lot_id' => $lote->id,
            'comedor_id' => $comedor->id,
            'cantidad_kg' => 5,
            'estado' => 'pendiente',
        ]);

        return compact(
            'donante',
            'otroDonante',
            'lote',
            'solicitud'
        );
    }

    public function test_donante_puede_aceptar_solicitud_y_descontar_inventario(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(AceptarSolicitudRescate::class);

        $resultado = $servicio->ejecutar(
            $datos['solicitud']->id,
            $datos['donante']->id
        );

        $this->assertEquals('aceptada', $resultado->estado);

        $this->assertEquals(
            25.00,
            (float) $datos['lote']->fresh()->disponible_kg
        );

        $this->assertNotNull($resultado->respondido_at);
    }

    public function test_otro_donante_no_puede_aceptar_solicitud_ajena(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(AceptarSolicitudRescate::class);

        try {
            $servicio->ejecutar(
                $datos['solicitud']->id,
                $datos['otroDonante']->id
            );

            $this->fail('Se esperaba una excepción de autorización.');
        } catch (AuthorizationException $e) {
            $this->assertDatabaseHas('solicitudes_rescate', [
                'id' => $datos['solicitud']->id,
                'estado' => 'pendiente',
            ]);

            $this->assertEquals(
                30.00,
                (float) $datos['lote']->fresh()->disponible_kg
            );
        }
    }


    public function test_no_se_puede_aceptar_una_solicitud_dos_veces(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(AceptarSolicitudRescate::class);

        // Primera aceptación
        $servicio->ejecutar(
            $datos['solicitud']->id,
            $datos['donante']->id
        );

        // Intentar aceptar nuevamente
        try {
            $servicio->ejecutar(
                $datos['solicitud']->id,
                $datos['donante']->id
            );

            $this->fail('Se esperaba que se rechazara la segunda aceptación.');
        } catch (\RuntimeException $e) {
            $this->assertDatabaseHas('solicitudes_rescate', [
                'id' => $datos['solicitud']->id,
                'estado' => 'aceptada',
            ]);

            $this->assertEquals(
                25.00,
                (float) $datos['lote']->fresh()->disponible_kg
            );
        }
    }


    public function test_no_se_puede_aceptar_una_solicitud_superior_al_stock_disponible(): void
    {
        $datos = $this->crearEscenario();

        // Reducimos el inventario a 3 kg.
        $datos['lote']->update([
            'disponible_kg' => 3,
        ]);

        $servicio = app(AceptarSolicitudRescate::class);

        try {
            // La solicitud es de 5 kg.
            $servicio->ejecutar(
                $datos['solicitud']->id,
                $datos['donante']->id
            );

            $this->fail('Se esperaba un rechazo por inventario insuficiente.');
        } catch (\RuntimeException $e) {
            $this->assertDatabaseHas('solicitudes_rescate', [
                'id' => $datos['solicitud']->id,
                'estado' => 'pendiente',
            ]);

            $this->assertEquals(
                3.00,
                (float) $datos['lote']->fresh()->disponible_kg
            );
        }
    }


    public function test_no_se_puede_aceptar_un_lote_vencido(): void
    {
        $datos = $this->crearEscenario();

        $datos['lote']->update([
            'expiracion' => now()->subDay(),
        ]);

        $servicio = app(AceptarSolicitudRescate::class);

        try {
            $servicio->ejecutar(
                $datos['solicitud']->id,
                $datos['donante']->id
            );

            $this->fail('Se esperaba un rechazo por lote vencido.');
        } catch (\RuntimeException $e) {
            $this->assertDatabaseHas('solicitudes_rescate', [
                'id' => $datos['solicitud']->id,
                'estado' => 'pendiente',
            ]);

            $this->assertEquals(
                30.00,
                (float) $datos['lote']->fresh()->disponible_kg
            );
        }
    }


    public function test_no_se_puede_aceptar_un_lote_fuera_de_fecha_de_recojo(): void
    {
        $datos = $this->crearEscenario();

        $datos['lote']->update([
            'recojo_maximo' => now()->subDay(),
        ]);

        $servicio = app(AceptarSolicitudRescate::class);

        try {
            $servicio->ejecutar(
                $datos['solicitud']->id,
                $datos['donante']->id
            );

            $this->fail('Se esperaba un rechazo por fecha máxima de recojo superada.');
        } catch (\RuntimeException $e) {
            $this->assertDatabaseHas('solicitudes_rescate', [
                'id' => $datos['solicitud']->id,
                'estado' => 'pendiente',
            ]);

            $this->assertEquals(
                30.00,
                (float) $datos['lote']->fresh()->disponible_kg
            );
        }
    }


    public function test_no_se_puede_aceptar_un_lote_que_vence_exactamente_ahora(): void
    {
        $ahora = now()->startOfSecond();

        Carbon::setTestNow($ahora);

        try {
            $datos = $this->crearEscenario();

            $datos['lote']->update([
                'expiracion' => $ahora,
            ]);

            $servicio = app(AceptarSolicitudRescate::class);

            try {
                $servicio->ejecutar(
                    $datos['solicitud']->id,
                    $datos['donante']->id
                );

                $this->fail('Se esperaba un rechazo por vencimiento.');
            } catch (\RuntimeException $e) {
                $this->assertDatabaseHas('solicitudes_rescate', [
                    'id' => $datos['solicitud']->id,
                    'estado' => 'pendiente',
                ]);

                $this->assertEquals(
                    30.00,
                    (float) $datos['lote']->fresh()->disponible_kg
                );
            }
        } finally {
            Carbon::setTestNow();
        }
    }


    public function test_no_se_puede_aceptar_un_lote_cuando_el_plazo_de_recojo_termina_exactamente_ahora(): void
    {
        $ahora = now()->startOfSecond();

        Carbon::setTestNow($ahora);

        try {
            $datos = $this->crearEscenario();

            $datos['lote']->update([
                'recojo_maximo' => $ahora,
            ]);

            $servicio = app(AceptarSolicitudRescate::class);

            try {
                $servicio->ejecutar(
                    $datos['solicitud']->id,
                    $datos['donante']->id
                );

                $this->fail('Se esperaba un rechazo por plazo de recojo vencido.');
            } catch (\RuntimeException $e) {
                $this->assertDatabaseHas('solicitudes_rescate', [
                    'id' => $datos['solicitud']->id,
                    'estado' => 'pendiente',
                ]);

                $this->assertEquals(
                    30.00,
                    (float) $datos['lote']->fresh()->disponible_kg
                );
            }
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_no_se_puede_aceptar_un_lote_agotado(): void
    {
        $datos = $this->crearEscenario();

        $datos['lote']->update([
            'estado' => 'agotado',
            'disponible_kg' => 0,
        ]);

        $servicio = app(AceptarSolicitudRescate::class);

        try {
            $servicio->ejecutar(
                $datos['solicitud']->id,
                $datos['donante']->id
            );

            $this->fail('Se esperaba un rechazo porque el lote está agotado.');
        } catch (\RuntimeException $e) {
            $this->assertDatabaseHas('solicitudes_rescate', [
                'id' => $datos['solicitud']->id,
                'estado' => 'pendiente',
            ]);

            $this->assertEquals(
                0.00,
                (float) $datos['lote']->fresh()->disponible_kg
            );
        }
    }

    public function test_no_se_puede_aceptar_un_lote_con_estado_no_disponible(): void
    {
        $datos = $this->crearEscenario();

        $datos['lote']->update([
            'estado' => 'pausado',
        ]);

        $servicio = app(AceptarSolicitudRescate::class);

        try {
            $servicio->ejecutar(
                $datos['solicitud']->id,
                $datos['donante']->id
            );

            $this->fail('Se esperaba un rechazo porque el lote no está disponible.');
        } catch (\RuntimeException $e) {
            $this->assertDatabaseHas('solicitudes_rescate', [
                'id' => $datos['solicitud']->id,
                'estado' => 'pendiente',
            ]);

            $this->assertEquals(
                30.00,
                (float) $datos['lote']->fresh()->disponible_kg
            );
        }
    }

    public function test_no_se_puede_aceptar_una_solicitud_con_cantidad_cero_o_negativa(): void
    {
        $datos = $this->crearEscenario();

        $servicio = app(AceptarSolicitudRescate::class);

        foreach ([0, -2] as $cantidad) {

            $datos['solicitud']->update([
                'cantidad_kg' => $cantidad,
            ]);

            try {
                $servicio->ejecutar(
                    $datos['solicitud']->id,
                    $datos['donante']->id
                );

                $this->fail(
                    "Se esperaba un rechazo para la cantidad: {$cantidad} kg."
                );
            } catch (\RuntimeException $e) {
                $this->assertDatabaseHas('solicitudes_rescate', [
                    'id' => $datos['solicitud']->id,
                    'estado' => 'pendiente',
                ]);

                $this->assertEquals(
                    30.00,
                    (float) $datos['lote']->fresh()->disponible_kg
                );
            }
        }
    }

    public function test_al_aceptar_todo_el_stock_el_lote_pasa_a_estado_agotado(): void
    {
        $datos = $this->crearEscenario();

        $datos['solicitud']->update([
            'cantidad_kg' => 30,
        ]);

        $servicio = app(AceptarSolicitudRescate::class);

        $resultado = $servicio->ejecutar(
            $datos['solicitud']->id,
            $datos['donante']->id
        );

        $loteActualizado = $datos['lote']->fresh();

        $this->assertEquals('aceptada', $resultado->estado);

        $this->assertEquals(
            0.00,
            (float) $loteActualizado->disponible_kg
        );

        $this->assertEquals('agotado', $loteActualizado->estado);
    }
}
