<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class UsuarioController extends Controller
{
    /**
     * 📋 Listar usuarios del propietario logueado
     * Si recibe ?solo_libres=1 → solo usuarios SIN trabajador asignado
     */
    public function index(Request $request)
    {
        $propietarioId = Auth::user()->propietario->id_propietario;

        $query = Usuario::where('id_propietario', $propietarioId);

        // 🟦 Si viene solo_libres=1 → mostrar solo usuarios que NO tengan trabajador
        if ($request->boolean('solo_libres')) {
            $query->whereDoesntHave('trabajador');
        }

        $usuarios = $query
            ->select('id_usuario', 'estado_usuario')
            ->orderBy('id_usuario')
            ->get();

        // Para solicitudes AJAX / JSON
        if ($request->wantsJson()) {
            return response()->json(['usuarios' => $usuarios]);
        }

        // Para las vistas Inertia
        return inertia('Propietario/Melamina/Usuarios', [
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * ➕ Registrar usuario para el propietario logueado
     */
    public function store(Request $request)
    {
        $propietarioId = Auth::user()->propietario->id_propietario;

        $validated = $request->validate([
            'id_usuario'     => 'required|string|max:100|unique:usuario,id_usuario',
            'contrasenia'    => 'required|string|max:100',
            'estado_usuario' => 'required|boolean',
        ]);

        $usuario = Usuario::create([
            'id_usuario'     => $validated['id_usuario'],
            // 🔐 Encriptación segura
            'contrasenia'    => Hash::make($validated['contrasenia']),
            'estado_usuario' => $validated['estado_usuario'],
            'id_propietario' => $propietarioId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado correctamente',
            'usuario' => $usuario,
        ]);
    }

    /**
     * ✏️ Actualizar usuario (solo si pertenece al propietario logueado)
     */
    public function update(Request $request, $id_usuario)
    {
        $propietarioId = Auth::user()->propietario->id_propietario;

        $usuario = Usuario::where('id_propietario', $propietarioId)
            ->findOrFail($id_usuario);

        $validated = $request->validate([
            'contrasenia'    => 'nullable|string|max:100',
            'estado_usuario' => 'required|boolean',
        ]);

        if (!empty($validated['contrasenia'])) {
            $usuario->contrasenia = Hash::make($validated['contrasenia']);
        }

        $usuario->estado_usuario = $validated['estado_usuario'];
        $usuario->save();

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'usuario' => $usuario,
        ]);
    }

    /**
     * 🔁 Cambiar estado rápido (solo sus usuarios)
     */
    public function toggleEstado($id_usuario)
    {
        $propietarioId = Auth::user()->propietario->id_propietario;

        $usuario = Usuario::where('id_propietario', $propietarioId)
            ->findOrFail($id_usuario);

        $usuario->estado_usuario = !$usuario->estado_usuario;
        $usuario->save();

        return response()->json([
            'success' => true,
            'usuario' => $usuario,
        ]);
    }

    /**
     * 🗑️ Eliminar usuario (solo sus usuarios)
     */
    public function destroy($id_usuario)
    {
        $propietarioId = Auth::user()->propietario->id_propietario;

        $usuario = Usuario::where('id_propietario', $propietarioId)
            ->findOrFail($id_usuario);

        $usuario->delete(); // Cascade delete

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado correctamente',
        ]);
    }
}
