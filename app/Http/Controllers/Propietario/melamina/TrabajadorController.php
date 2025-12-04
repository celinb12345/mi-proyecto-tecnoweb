<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Trabajador;
use App\Models\Usuario;

class TrabajadorController extends Controller
{
    // LISTAR TRABAJADORES DEL PROPIETARIO
    public function index(Request $request)
    {
        $query = Trabajador::paraPropietario(); // usa el scope + relación usuario

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_trabajador', 'ILIKE', "%$buscar%")
                  ->orWhere('apellido_trabajador', 'ILIKE', "%$buscar%")
                  ->orWhere('id_usuario', 'ILIKE', "%$buscar%");
            });
        }

        $trabajadores = $query->orderBy('id_trabajador', 'asc')->get();

        return response()->json([
            'trabajadores' => $trabajadores,
        ]);
    }

    /**
     * Usuarios del propietario logueado que aún NO son
     * propietario/cliente/trabajador → para el <select>
     */
    // App\Http\Controllers\Propietario\Melamina\TrabajadorController.php

public function usuariosDisponibles()
{
    $propietarioId = Auth::user()->propietario->id_propietario;

    $usuarios = Usuario::query()
        // solo usuarios creados por este propietario
        ->where('id_propietario', $propietarioId)
        // solo los que son de tipo trabajador (prefijo trab_m)
        ->where('id_usuario', 'LIKE', 'trab_m%')
        // que todavía NO tengan registro en la tabla trabajador
        ->whereDoesntHave('trabajador')
        ->orderBy('id_usuario')
        ->get(['id_usuario']);

    return response()->json(['usuarios' => $usuarios]);
}


    // REGISTRAR TRABAJADOR
    public function store(Request $request)
    {
        $propietarioId = Auth::user()->propietario->id_propietario;

        $validated = $request->validate([
            'id_usuario'             => 'required|string|exists:usuario,id_usuario',
            'nombre_trabajador'      => 'required|string|max:100',
            'apellido_trabajador'    => 'required|string|max:100',
            'cargo_trabajador'       => 'required|string|max:80',
            'sueldo_trabajador'      => 'required|numeric|min:0',
            'telefono_trabajador'    => 'nullable|string|max:20',
            'tipo_contrato_trab'     => 'required|string|max:50',
            'saldo_pendiente_trab'   => 'nullable|numeric|min:0',
            'foto'                   => 'nullable|image|max:2048',
            'codigoqr'               => 'nullable|image|max:2048',
        ]);

        // que ese usuario no tenga ya un trabajador
        if (Trabajador::where('id_usuario', $validated['id_usuario'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Este usuario ya está registrado como trabajador.',
            ], 422);
        }

        $trab = new Trabajador($validated);
        $trab->id_propietario = $propietarioId;   // 👈 directo, sin if

        if ($request->hasFile('foto')) {
            $trab->foto_trabajador = $request->file('foto')
                ->store('trabajadores/fotos', 'public');
        }

        if ($request->hasFile('codigoqr')) {
            $trab->codigoqr_trab = $request->file('codigoqr')
                ->store('trabajadores/qr', 'public');
        }

        $trab->save();

        return response()->json([
            'success'    => true,
            'trabajador' => $trab,
        ]);
    }

    // ACTUALIZAR
    public function update(Request $request, $id)
    {
        $trab = Trabajador::paraPropietario()->findOrFail($id);

        $validated = $request->validate([
            'nombre_trabajador'      => 'required|string|max:100',
            'apellido_trabajador'    => 'required|string|max:100',
            'cargo_trabajador'       => 'required|string|max:80',
            'sueldo_trabajador'      => 'required|numeric|min:0',
            'telefono_trabajador'    => 'nullable|string|max:20',
            'tipo_contrato_trab'     => 'required|string|max:50',
            'saldo_pendiente_trab'   => 'nullable|numeric|min:0',
            'foto'                   => 'nullable|image|max:2048',
            'codigoqr'               => 'nullable|image|max:2048',
        ]);

        $trab->fill($validated);

        if ($request->hasFile('foto')) {
            if ($trab->foto_trabajador) {
                Storage::disk('public')->delete($trab->foto_trabajador);
            }
            $trab->foto_trabajador = $request->file('foto')
                ->store('trabajadores/fotos', 'public');
        }

        if ($request->hasFile('codigoqr')) {
            if ($trab->codigoqr_trab) {
                Storage::disk('public')->delete($trab->codigoqr_trab);
            }
            $trab->codigoqr_trab = $request->file('codigoqr')
                ->store('trabajadores/qr', 'public');
        }

        $trab->save();

        return response()->json([
            'success'    => true,
            'trabajador' => $trab,
        ]);
    }

    // ELIMINAR
    public function destroy($id)
    {
        $trab = Trabajador::paraPropietario()->findOrFail($id);

        if ($trab->foto_trabajador) {
            Storage::disk('public')->delete($trab->foto_trabajador);
        }
        if ($trab->codigoqr_trab) {
            Storage::disk('public')->delete($trab->codigoqr_trab);
        }

        $trab->delete();

        return response()->json(['success' => true]);
    }
}
