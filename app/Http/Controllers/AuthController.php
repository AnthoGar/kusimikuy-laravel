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

        $rol = Role::where('slug', $datos['account_type'])->firstOrFail();

        $usuario = DB::transaction(function () use ($datos, $rol) {
            $usuario = User::create([
                'name' => $datos['name'],
                'email' => $datos['email'],
                'password' => $datos['password'],
                'phone' => $datos['phone'] ?? null,
                'role_id' => $rol->id,
                'is_active' => true,
            ]);

            if ($rol->slug === 'comedor') {
                ComedorPopular::create([
                    'user_id' => $usuario->id,
                    'nombre' => $datos['nombre'],
                    'direccion' => $datos['direccion'],
                    'distrito' => $datos['distrito'],
                    'raciones_diarias' => $datos['raciones_diarias'],
                    'tiene_refrigeracion' => $datos['tiene_refrigeracion'] ?? false,
                    'nombre_contacto' => $datos['nombre_contacto'] ?? null,
                ]);
            }

            return $usuario;
        });

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()
            ->route($this->dashboardRouteFor($usuario))
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

        $credenciales['is_active'] = true;

        if (!Auth::attempt($credenciales, $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'email' => 'Las credenciales son incorrectas o la cuenta está inactiva.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $usuario = Auth::user()->load('role');

        return redirect()
            ->intended(route($this->dashboardRouteFor($usuario)))
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

    private function dashboardRouteFor(User $user): string
    {
        return match ($user->role?->slug) {
            'donante' => 'donante.dashboard',
            'comedor' => 'comedor.dashboard',
            'colaborador' => 'colaborador.dashboard',
            'administrador' => 'admin.dashboard',
            default => 'home',
        };
    }
}
