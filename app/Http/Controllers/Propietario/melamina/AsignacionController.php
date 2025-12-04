<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Asignacion;
use App\Models\Trabajador;
use App\Models\Proyecto;

class AsignacionController extends Controller
{
    // GET /propietario/melamina/asignaciones
    public function index()
    {
        $idProp = Auth::user()->propietario->id_propietario;

        // 🔹 Asignaciones SOLO de proyectos de este propietario
        $asignaciones = Asignacion::whereHas('proyecto', function ($q) use ($idProp) {
                $q->where('id_propietario', $idProp);
            })
            ->with(['trabajador', 'proyecto'])
            ->orderBy('fecha_asignacion', 'desc')
            ->get();

        // 🔹 Trabajadores SOLO de este propietario
        $trabajadores = Trabajador::paraPropietario()
            ->orderBy('nombre_trabajador')
            ->get(['id_trabajador', 'nombre_trabajador', 'apellido_trabajador']);

        // 🔹 Proyectos SOLO de este propietario
        $proyectos = Proyecto::paraPropietario()
            ->orderBy('nombre_pro')
            ->get(['id_proyecto', 'nombre_pro']);

        return response()->json([
            'asignaciones' => $asignaciones,
            'trabajadores' => $trabajadores,
            'proyectos'    => $proyectos,
        ]);
    }

    // POST /propietario/melamina/asignaciones
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_asignacion' => 'required|date',
            'id_trabajador'    => 'required|integer',
            'id_proyecto'      => 'required|integer',
        ]);

        $idProp = Auth::user()->propietario->id_propietario;

        // ✅ Validar que el trabajador y el proyecto sean del MISMO propietario
        $trabajador = Trabajador::paraPropietario()
            ->where('id_trabajador', $validated['id_trabajador'])
            ->firstOrFail();

        $proyecto = Proyecto::paraPropietario()
            ->where('id_proyecto', $validated['id_proyecto'])
            ->firstOrFail();

        $asignacion = Asignacion::create([
            'fecha_asignacion' => $validated['fecha_asignacion'],
            'id_trabajador'    => $trabajador->id_trabajador,
            'id_proyecto'      => $proyecto->id_proyecto,
        ]);

        return response()->json([
            'success'     => true,
            'asignacion'  => $asignacion,
            'message'     => 'Asignación registrada correctamente',
        ]);
    }

    // POST /propietario/melamina/asignaciones/{id}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'fecha_asignacion' => 'required|date',
            'id_trabajador'    => 'required|integer',
            'id_proyecto'      => 'required|integer',
        ]);

        $idProp = Auth::user()->propietario->id_propietario;

        $asignacion = Asignacion::whereHas('proyecto', function ($q) use ($idProp) {
                $q->where('id_propietario', $idProp);
            })
            ->findOrFail($id);

        $trabajador = Trabajador::paraPropietario()
            ->where('id_trabajador', $validated['id_trabajador'])
            ->firstOrFail();

        $proyecto = Proyecto::paraPropietario()
            ->where('id_proyecto', $validated['id_proyecto'])
            ->firstOrFail();

        $asignacion->update([
            'fecha_asignacion' => $validated['fecha_asignacion'],
            'id_trabajador'    => $trabajador->id_trabajador,
            'id_proyecto'      => $proyecto->id_proyecto,
        ]);

        return response()->json([
            'success'    => true,
            'asignacion' => $asignacion,
            'message'    => 'Asignación actualizada correctamente',
        ]);
    }

    // DELETE /propietario/melamina/asignaciones/{id}
    public function destroy($id)
    {
        $idProp = Auth::user()->propietario->id_propietario;

        $asignacion = Asignacion::whereHas('proyecto', function ($q) use ($idProp) {
                $q->where('id_propietario', $idProp);
            })
            ->findOrFail($id);

        $asignacion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asignación eliminada correctamente',
        ]);
    }
}
