<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Proyecto;

class CompraController extends Controller
{
    // LISTAR COMPRAS + PROVEEDORES + PROYECTOS
    public function index(Request $request)
    {
        $user = Auth::user();
        $idPropietario = $user->propietario->id_propietario ?? null;

        $query = Compra::with(['proveedor', 'proyecto']);

        if ($idPropietario) {
            $query->where('id_propietario', $idPropietario);
        }

        if ($request->filled('desde')) {
            $query->where('fecha_compra', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->where('fecha_compra', '<=', $request->hasta);
        }

        $compras = $query->orderBy('fecha_compra', 'desc')->get();

        // Proveedores del propietario
        $proveedoresQuery = Proveedor::query();
        if ($idPropietario) {
            $proveedoresQuery->where('id_propietario', $idPropietario);
        }

        $proveedores = $proveedoresQuery
            ->orderBy('nombre_empres_prov')
            ->get(['id_proveedor', 'nombre_empres_prov']);

        // Proyectos del propietario
        $proyectosQuery = Proyecto::query();
        if ($idPropietario) {
            $proyectosQuery->where('id_propietario', $idPropietario);
        }

        $proyectos = $proyectosQuery
            ->orderBy('nombre_pro')
            ->get(['id_proyecto', 'nombre_pro']);

        return response()->json([
            'compras'     => $compras,
            'proveedores' => $proveedores,
            'proyectos'   => $proyectos,
        ]);
    }

    // REGISTRAR COMPRA
    public function store(Request $request)
    {
        $user = Auth::user();
        $idPropietario = $user->propietario->id_propietario ?? null;

        // ⚠ Normalizar id_proyecto: si viene '' lo convertimos a null
        $request->merge([
            'id_proyecto' => $request->id_proyecto ?: null,
        ]);

        $validated = $request->validate([
            'fecha_compra'      => 'required|date',
            'metodo_pago_comp'  => 'required|string|max:50',
            'estado_compra'     => 'required|string|max:20',
            'observacion_comp'  => 'nullable|string',
            'id_proveedor'      => 'required|exists:proveedor,id_proveedor',
            'id_proyecto'       => 'nullable|integer|exists:proyecto,id_proyecto',
        ]);

        $validated['id_propietario'] = $idPropietario;
        $validated['total_compra']   = 0; // luego se actualiza con los detalles

        $compra = Compra::create($validated);

        return response()->json([
            'success' => true,
            'compra'  => $compra,
            'message' => 'Compra registrada correctamente',
        ]);
    }

    // ACTUALIZAR COMPRA
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $idPropietario = $user->propietario->id_propietario ?? null;

        $compra = Compra::where('id_compra', $id)
            ->when($idPropietario, fn($q) => $q->where('id_propietario', $idPropietario))
            ->firstOrFail();

        // ⚠ Normalizar id_proyecto también aquí
        $request->merge([
            'id_proyecto' => $request->id_proyecto ?: null,
        ]);

        $validated = $request->validate([
            'fecha_compra'      => 'required|date',
            'metodo_pago_comp'  => 'required|string|max:50',
            'estado_compra'     => 'required|string|max:20',
            'observacion_comp'  => 'nullable|string',
            'id_proveedor'      => 'required|exists:proveedor,id_proveedor',
            'id_proyecto'       => 'nullable|integer|exists:proyecto,id_proyecto',
        ]);

        $compra->fill($validated)->save();

        return response()->json([
            'success' => true,
            'compra'  => $compra,
            'message' => 'Compra actualizada correctamente',
        ]);
    }

    // ELIMINAR COMPRA
    public function destroy($id)
    {
        $user = Auth::user();
        $idPropietario = $user->propietario->id_propietario ?? null;

        $compra = Compra::where('id_compra', $id)
            ->when($idPropietario, fn($q) => $q->where('id_propietario', $idPropietario))
            ->firstOrFail();

        $compra->delete();

        return response()->json([
            'success' => true,
            'message' => 'Compra eliminada correctamente',
        ]);
    }
}
