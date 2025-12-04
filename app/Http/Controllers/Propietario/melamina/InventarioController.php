<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\UnidadMedida;

class InventarioController extends Controller
{
    // LISTAR INVENTARIO + PRODUCTOS + UNIDADES
    public function index(Request $request)
    {
        $query = Inventario::paraPropietario()->with(['producto', 'unidad']);

        // filtro por tipo de movimiento: entrada / salida
        if ($request->filled('tipo')) {
            $tipo = $request->tipo; // 'entrada' | 'salida'
            if ($tipo === 'entrada') {
                $query->where('tipo_movimiento', true);
            } elseif ($tipo === 'salida') {
                $query->where('tipo_movimiento', false);
            }
        }

        $inventarios = $query->orderBy('fecha_inv', 'desc')->get();

        $productos = Producto::paraPropietario()
            ->orderBy('nombre_prod')
            ->get(['id_producto', 'nombre_prod']);

        $unidades = UnidadMedida::orderBy('nombre_unidad')
            ->get(['id_unidad', 'nombre_unidad', 'abreviatura_unidad', 'descripcion_unidad', 'estado_unidad']);

        return response()->json([
            'inventarios' => $inventarios,
            'productos'   => $productos,
            'unidades'    => $unidades,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_movimiento' => 'required|in:entrada,salida',
            'cantidad_inv'    => 'required|numeric|min:0.01',
            'fecha_inv'       => 'required|date',
            'id_producto'     => 'required|exists:producto,id_producto',
            'id_unidad'       => 'required|exists:unidad_medida,id_unidad',
        ]);

        $tipoBool = $validated['tipo_movimiento'] === 'entrada';

        $inventario = Inventario::create([
            'id_propietario' => Auth::user()->propietario->id_propietario,
            'tipo_movimiento' => $tipoBool,
            'cantidad_inv'    => $validated['cantidad_inv'],
            'fecha_inv'       => $validated['fecha_inv'],
            'id_producto'     => $validated['id_producto'],
            'id_unidad'       => $validated['id_unidad'],
        ]);

        return response()->json([
            'success'    => true,
            'inventario' => $inventario,
            'message'    => 'Movimiento de inventario registrado correctamente',
        ]);
    }

    public function update(Request $request, $id)
    {
        $inventario = Inventario::paraPropietario()->findOrFail($id);

        $validated = $request->validate([
            'tipo_movimiento' => 'required|in:entrada,salida',
            'cantidad_inv'    => 'required|numeric|min:0.01',
            'fecha_inv'       => 'required|date',
            'id_producto'     => 'required|exists:producto,id_producto',
            'id_unidad'       => 'required|exists:unidad_medida,id_unidad',
        ]);

        $tipoBool = $validated['tipo_movimiento'] === 'entrada';

        $inventario->fill([
            'tipo_movimiento' => $tipoBool,
            'cantidad_inv'    => $validated['cantidad_inv'],
            'fecha_inv'       => $validated['fecha_inv'],
            'id_producto'     => $validated['id_producto'],
            'id_unidad'       => $validated['id_unidad'],
        ])->save();

        return response()->json([
            'success'    => true,
            'inventario' => $inventario,
            'message'    => 'Movimiento de inventario actualizado correctamente',
        ]);
    }

    public function destroy($id)
    {
        $inventario = Inventario::paraPropietario()->findOrFail($id);
        $inventario->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro de inventario eliminado correctamente',
        ]);
    }
}
