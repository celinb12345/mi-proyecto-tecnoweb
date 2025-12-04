<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return inertia('Auth/Login');
    }

    public function login(Request $request)
    {
        // Validar datos de entrada
        $credentials = $request->validate([
            'id_usuario' => 'required|string',
            'password'   => 'required|string',
        ]);

        Log::info('Intento de login', ['id_usuario' => $credentials['id_usuario']]);

        // Auth::attempt usará:
        // - id_usuario como identificador (getAuthIdentifierName)
        // - contrasenia como contraseña hasheada (getAuthPassword)
        if (! Auth::attempt($credentials)) {
            Log::warning('Credenciales inválidas', ['id_usuario' => $credentials['id_usuario']]);

            return back()
                ->withErrors(['id_usuario' => 'Las credenciales proporcionadas no son correctas.'])
                ->withInput($request->only('id_usuario'));
        }

        // Seguridad: regenerar sesión
        $request->session()->regenerate();

        /** @var \App\Models\Usuario $user */
        $user = Auth::user();
        $user->load(['propietario', 'cliente', 'trabajador', 'proveedor']);

        // Usuario desactivado
        if (! $user->estado_usuario) {
            Log::warning('Usuario desactivado', ['id_usuario' => $user->id_usuario]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'id_usuario' => 'Su cuenta está desactivada.',
            ]);
        }

        Log::info('Login exitoso', [
            'id_usuario' => $user->id_usuario,
            'role'       => $user->role,
        ]);

        return $this->redirectByRole($user);
    }

    protected function redirectByRole($user)
    {
        $role = $user->role;

        switch ($role) {
            case 'propietario':
                $propietario = $user->propietario;
                $especialidad = strtolower($propietario->especialidad_propietario ?? '');

                if (strpos($especialidad, 'melamina') !== false) {
                    return redirect()->route('propietario.melamina.dashboard');
                }

                if (
                    strpos($especialidad, 'construcción') !== false ||
                    strpos($especialidad, 'construccion') !== false
                ) {
                    return redirect()->route('propietario.construccion.dashboard');
                }

                return redirect()->route('dashboard');

            case 'cliente':
                return redirect()->route('cliente.dashboard');

            case 'trabajador':
                return redirect()->route('trabajador.dashboard');

            case 'proveedor':
                return redirect()->route('proveedor.dashboard');

            default:
                return redirect()->route('dashboard');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
