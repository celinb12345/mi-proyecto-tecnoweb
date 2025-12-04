<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Proyecto;
use App\Models\Cliente;

class ProyectoController extends Controller
{
    // LISTAR PROYECTOS + CLIENTES
    public function index(Request $request)
    {
        Proyecto::actualizarEstadosPorFecha();

        $query = Proyecto::paraPropietario()->with('cliente');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where('nombre_pro', 'ILIKE', "%{$buscar}%");
        }

        if ($request->filled('desde')) {
            $query->where('fecha_inicio_pro', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->where('fecha_inicio_pro', '<=', $request->hasta);
        }

        $proyectos = $query->orderBy('fecha_inicio_pro', 'desc')->get();

        $clientes = Cliente::paraPropietario()
            ->orderBy('nombre_cli')
            ->get(['id_cliente', 'nombre_cli', 'apellido_cli']);

        return response()->json([
            'proyectos' => $proyectos,
            'clientes'  => $clientes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_pro'        => 'required|string|max:100',
            'descripcion_pro'   => 'nullable|string|max:200',
            'fecha_inicio_pro'  => 'required|date',
            'fecha_fin_pro'     => 'nullable|date|after_or_equal:fecha_inicio_pro',
            'direccion_pro'     => 'nullable|string|max:200',
            'monto_total_pro'   => 'nullable|numeric|min:0',
            'estado_proyecto'   => 'required|string|max:30',
            'obra_completa'     => 'required|boolean',
            'id_cliente'        => 'required|exists:cliente,id_cliente',
        ]);

        $validated['id_propietario'] = Auth::user()->propietario->id_propietario;

        $proyecto = Proyecto::create($validated);

        return response()->json([
            'success'  => true,
            'proyecto' => $proyecto,
            'message'  => 'Proyecto registrado correctamente',
        ]);
    }

    public function update(Request $request, $id)
    {
        $proyecto = Proyecto::paraPropietario()->findOrFail($id);

        $validated = $request->validate([
            'nombre_pro'        => 'required|string|max:100',
            'descripcion_pro'   => 'nullable|string|max:200',
            'fecha_inicio_pro'  => 'required|date',
            'fecha_fin_pro'     => 'nullable|date|after_or_equal:fecha_inicio_pro',
            'direccion_pro'     => 'nullable|string|max:200',
            'monto_total_pro'   => 'nullable|numeric|min:0',
            'estado_proyecto'   => 'required|string|max:30',
            'obra_completa'     => 'required|boolean',
            'id_cliente'        => 'required|exists:cliente,id_cliente',
        ]);

        $proyecto->fill($validated)->save();

        return response()->json([
            'success'  => true,
            'proyecto' => $proyecto,
            'message'  => 'Proyecto actualizado correctamente',
        ]);
    }

    public function destroy($id)
    {
        $proyecto = Proyecto::paraPropietario()->findOrFail($id);
        $proyecto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proyecto eliminado correctamente',
        ]);
    }
}
