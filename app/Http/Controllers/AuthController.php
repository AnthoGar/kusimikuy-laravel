<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\ComedorPopular;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Mostrar el formulario de registro.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Registrar un nuevo usuario.
     */
    public function register(Request $request)
    {
        // 1. Validar los datos recibidos.
        $datos = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',

            'password' => 'required|string|min:8|confirmed',

            'account_type' => 'required|in:donante,comedor',

            // Campos exclusivos de los comedores.
            'nombre' => 'required_if:account_type,comedor|nullable|string|max:150',
            'direccion' => 'required_if:account_type,comedor|nullable|string|max:255',
            'distrito' => 'required_if:account_type,comedor|nullable|string|max:100',
            'raciones_diarias' => 'required_if:account_type,comedor|nullable|integer|min:1',
            'tiene_refrigeracion' => 'sometimes|boolean',
            'nombre_contacto' => 'nullable|string|max:150',
        ]);

        // 2. Buscar el rol permitido.
        $rol = Role::where('slug', $datos['account_type'])
            ->firstOrFail();

        // 3. Crear los registros dentro de una transacción.
        $usuario = DB::transaction(function () use ($datos, $rol) {

            // Crear el usuario.
            $usuario = User::create([
                'name' => $datos['name'],
                'email' => $datos['email'],
                'password' => $datos['password'],
                'phone' => $datos['phone'] ?? null,
                'role_id' => $rol->id,
                'is_active' => true,
            ]);

            // Si es un comedor, crear su información adicional.
            if ($rol->slug === 'comedor') {

                ComedorPopular::create([
                    'user_id' => $usuario->id,
                    'nombre' => $datos['nombre'],
                    'direccion' => $datos['direccion'],
                    'distrito' => $datos['distrito'],
                    'raciones_diarias' => $datos['raciones_diarias'],
                    'tiene_refrigeracion' =>
                    $datos['tiene_refrigeracion'] ?? false,
                    'nombre_contacto' =>
                    $datos['nombre_contacto'] ?? null,
                ]);
            }

            return $usuario;
        });

        // 4. Iniciar sesión automáticamente.
        Auth::login($usuario);

        // Regenerar la sesión por seguridad.
        $request->session()->regenerate();

        return redirect()
            ->route('home')
            ->with('success', '¡Tu cuenta ha sido creada correctamente!');
    }
    
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Solo permitimos iniciar sesión a cuentas activas.
        $credenciales['is_active'] = true;

        if (!Auth::attempt(
            $credenciales,
            $request->boolean('remember')
        )) {
            return back()
                ->withErrors([
                    'email' => 'Las credenciales son incorrectas o la cuenta está inactiva.',
                ])
                ->onlyInput('email');
        }

        // Regeneramos la sesión para prevenir ataques de fijación de sesión.
        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('success', '¡Bienvenido nuevamente a KusiMikuy!');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Has cerrado sesión correctamente.');
    }
}
