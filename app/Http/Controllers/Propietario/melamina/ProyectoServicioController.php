<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProyectoServicio;
use App\Models\Proyecto;
use App\Models\Servicio;

class ProyectoServicioController extends Controller
{
    // GET /propietario/melamina/proyecto-servicios
    public function index()
    {
        $idProp = Auth::user()->propietario->id_propietario;

        // 🔹 Detalles SOLO de proyectos de este propietario
        $detalles = ProyectoServicio::whereHas('proyecto', function ($q) use ($idProp) {
                $q->where('id_propietario', $idProp);
            })
            ->with(['proyecto', 'servicio'])
            ->orderBy('id_proyecto_servicio', 'desc')
            ->get();

        // 🔹 Proyectos SOLO del propietario logueado
        $proyectos = Proyecto::paraPropietario()
            ->orderBy('nombre_pro')
            ->get(['id_proyecto', 'nombre_pro']);

        // 🔹 Servicios SOLO del propietario logueado
        $servicios = Servicio::paraPropietario()
            ->orderBy('nombre_servicio')
            ->get(['id_servicio', 'nombre_servicio', 'precio_serv']);

        return response()->json([
            'detalles'  => $detalles,
            'proyectos' => $proyectos,
            'servicios' => $servicios,
        ]);
    }

    // POST /propietario/melamina/proyecto-servicios
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_proyecto'     => 'required|integer',
            'id_servicio'     => 'required|integer',
            'precio_unitario' => 'required|numeric|min:0',
            'sub_total'       => 'nullable|numeric|min:0',
        ]);

        $idProp = Auth::user()->propietario->id_propietario;

        // ✅ Proyecto debe ser del propietario logueado
        $proyecto = Proyecto::paraPropietario()
            ->where('id_proyecto', $data['id_proyecto'])
            ->firstOrFail();

        // ✅ Servicio debe ser del propietario logueado
        $servicio = Servicio::paraPropietario()
            ->where('id_servicio', $data['id_servicio'])
            ->firstOrFail();

        if (empty($data['sub_total'])) {
            $data['sub_total'] = $data['precio_unitario'];
        }

        $detalle = ProyectoServicio::create([
            'id_proyecto'     => $proyecto->id_proyecto,
            'id_servicio'     => $servicio->id_servicio,
            'precio_unitario' => $data['precio_unitario'],
            'sub_total'       => $data['sub_total'],
        ]);

        return response()->json([
            'success' => true,
            'detalle' => $detalle,
            'message' => 'Detalle proyecto-servicio registrado correctamente',
        ]);
    }

    // POST /propietario/melamina/proyecto-servicios/{id}
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_proyecto'     => 'required|integer',
            'id_servicio'     => 'required|integer',
            'precio_unitario' => 'required|numeric|min:0',
            'sub_total'       => 'nullable|numeric|min:0',
        ]);

        $idProp = Auth::user()->propietario->id_propietario;

        // Solo se puede editar si el detalle pertenece a un proyecto del propietario
        $detalle = ProyectoServicio::whereHas('proyecto', function ($q) use ($idProp) {
                $q->where('id_propietario', $idProp);
            })
            ->findOrFail($id);

        $proyecto = Proyecto::paraPropietario()
            ->where('id_proyecto', $data['id_proyecto'])
            ->firstOrFail();

        $servicio = Servicio::paraPropietario()
            ->where('id_servicio', $data['id_servicio'])
            ->firstOrFail();

        if (empty($data['sub_total'])) {
            $data['sub_total'] = $data['precio_unitario'];
        }

        $detalle->update([
            'id_proyecto'     => $proyecto->id_proyecto,
            'id_servicio'     => $servicio->id_servicio,
            'precio_unitario' => $data['precio_unitario'],
            'sub_total'       => $data['sub_total'],
        ]);

        return response()->json([
            'success' => true,
            'detalle' => $detalle,
            'message' => 'Detalle proyecto-servicio actualizado correctamente',
        ]);
    }

    // DELETE /propietario/melamina/proyecto-servicios/{id}
    public function destroy($id)
    {
        $idProp = Auth::user()->propietario->id_propietario;

        $detalle = ProyectoServicio::whereHas('proyecto', function ($q) use ($idProp) {
                $q->where('id_propietario', $idProp);
            })
            ->findOrFail($id);

        $detalle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Detalle proyecto-servicio eliminado correctamente',
        ]);
    }
}
