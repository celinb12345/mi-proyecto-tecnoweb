<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Servicio;

class ServicioController extends Controller
{
    // LISTAR + BUSCAR (solo servicios del propietario logueado)
    public function index(Request $request)
    {
        $query = Servicio::paraPropietario();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where('nombre_servicio', 'ILIKE', "%{$buscar}%");
        }

        $servicios = $query->orderBy('nombre_servicio')->get();

        return response()->json([
            'servicios' => $servicios,
        ]);
    }

    // REGISTRAR SERVICIO
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_servicio'      => 'required|string|max:100',
            'descripcion_serv'     => 'nullable|string|max:200',
            'precio_serv'          => 'required|numeric|min:0',
            'tiempo_estimado_serv' => 'nullable|date',
        ]);

        // 🔐 asignar propietario dueño del servicio
        $validated['id_propietario'] = Auth::user()->propietario->id_propietario;

        $servicio = Servicio::create($validated);

        return response()->json([
            'success'  => true,
            'servicio' => $servicio,
            'message'  => 'Servicio registrado correctamente',
        ]);
    }

    // ACTUALIZAR SERVICIO (solo si es suyo)
    public function update(Request $request, $id)
    {
        $servicio = Servicio::paraPropietario()->findOrFail($id);

        $validated = $request->validate([
            'nombre_servicio'      => 'required|string|max:100',
            'descripcion_serv'     => 'nullable|string|max:200',
            'precio_serv'          => 'required|numeric|min:0',
            'tiempo_estimado_serv' => 'nullable|date',
        ]);

        $servicio->fill($validated)->save();

        return response()->json([
            'success'  => true,
            'servicio' => $servicio,
            'message'  => 'Servicio actualizado correctamente',
        ]);
    }

    // ELIMINAR SERVICIO (solo si es suyo)
    public function destroy($id)
    {
        $servicio = Servicio::paraPropietario()->findOrFail($id);
        $servicio->delete();

        return response()->json([
            'success' => true,
            'message' => 'Servicio eliminado correctamente',
        ]);
    }
}
