<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnidadMedida;

class UnidadMedidaController extends Controller
{
    public function index(Request $request)
    {
        $query = UnidadMedida::query();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_unidad', 'ILIKE', "%{$buscar}%")
                  ->orWhere('abreviatura_unidad', 'ILIKE', "%{$buscar}%");
            });
        }

        $unidades = $query->orderBy('nombre_unidad')->get();

        return response()->json([
            'unidades' => $unidades,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_unidad'      => 'required|string|max:50',
            'abreviatura_unidad' => 'required|string|max:10',
            'descripcion_unidad' => 'nullable|string|max:150',
            'estado_unidad'      => 'required|boolean',
        ]);

        $unidad = UnidadMedida::create($validated);

        return response()->json([
            'success' => true,
            'unidad'  => $unidad,
            'message' => 'Unidad de medida registrada correctamente',
        ]);
    }

    public function update(Request $request, $id)
    {
        $unidad = UnidadMedida::findOrFail($id);

        $validated = $request->validate([
            'nombre_unidad'      => 'required|string|max:50',
            'abreviatura_unidad' => 'required|string|max:10',
            'descripcion_unidad' => 'nullable|string|max:150',
            'estado_unidad'      => 'required|boolean',
        ]);

        $unidad->fill($validated)->save();

        return response()->json([
            'success' => true,
            'unidad'  => $unidad,
            'message' => 'Unidad de medida actualizada correctamente',
        ]);
    }

    public function destroy($id)
    {
        $unidad = UnidadMedida::findOrFail($id);
        $unidad->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unidad de medida eliminada correctamente',
        ]);
    }
}
