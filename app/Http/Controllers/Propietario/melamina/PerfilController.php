<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    // Actualizar perfil del propietario de MELAMINA
    public function update(Request $request)
    {
        /** @var \App\Models\Usuario $u */
            $u = Auth::user();
            $u->load('propietario');


        if (! $u || $u->role !== 'propietario') {
            abort(403, 'No tienes permiso para actualizar este perfil.');
        }

        $prop = $u->propietario;

        // Seguridad extra: solo melamina
        $especialidad = strtolower($prop->especialidad_propietario ?? '');
        if (strpos($especialidad, 'melamina') === false) {
            abort(403, 'Este perfil es solo para propietarios de melamina.');
        }

        $validated = $request->validate([
            'nombre_propietario'       => 'required|string|max:100',
            'apellido_propietario'     => 'required|string|max:100',
            'correo_propietario'       => 'nullable|email|max:120',
            'telefono_propietario'     => 'nullable|string|max:20',
            'direccion_propietario'    => 'nullable|string|max:150',
            'especialidad_propietario' => 'nullable|string|max:100',
            'foto'                     => 'nullable|image|max:2048', // 2MB
        ]);

        // Actualizar campos de texto
        $prop->nombre_propietario       = $validated['nombre_propietario'];
        $prop->apellido_propietario     = $validated['apellido_propietario'];
        $prop->correo_propietario       = $validated['correo_propietario'] ?? null;
        $prop->telefono_propietario     = $validated['telefono_propietario'] ?? null;
        $prop->direccion_propietario    = $validated['direccion_propietario'] ?? null;
        $prop->especialidad_propietario = $validated['especialidad_propietario'] ?? null;

        // FOTO
        if ($request->hasFile('foto')) {
            // borrar foto anterior si existe
            if ($prop->foto_propietario) {
                Storage::disk('public')->delete($prop->foto_propietario);
            }

            // se guarda en storage/app/public/propietarios
            // y en BD guardamos SOLO la ruta, ej: "propietarios/abc123.jpg"
            $path = $request->file('foto')->store('propietarios', 'public');
            $prop->foto_propietario = $path;
        }

        $prop->save();

        return response()->json([
            'success'     => true,
            'message'     => 'Perfil actualizado correctamente',
            'propietario' => $prop,
        ]);
    }
}
