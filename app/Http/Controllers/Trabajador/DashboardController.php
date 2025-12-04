<?php

namespace App\Http\Controllers\Trabajador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use App\Models\Trabajador;
use Illuminate\Support\Facades\Hash;


// Modelos
use App\Models\Proyecto;
use App\Models\Usuario;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var Usuario $u */
        $u = Auth::user();

        if (! $u) {
            return redirect()->route('login');
        }

        if ($u->role !== 'trabajador') {
            Auth::logout();
            return redirect()->route('login');
        }

        // Cargamos relación trabajador
        $u->load('trabajador');
        $trab = $u->trabajador;

        // ---------- PROYECTO ASIGNADO ----------
        $proyectoAsignado = null;

        if ($trab) {
            $proyectoAsignado = Proyecto::select(
                    'proyecto.id_proyecto',
                    'proyecto.nombre_pro',
                    'proyecto.descripcion_pro',
                    'proyecto.direccion_pro',
                    'proyecto.fecha_inicio_pro',
                    'proyecto.fecha_fin_pro',
                    'proyecto.estado_proyecto'
                )
                ->join('asignacion', 'asignacion.id_proyecto', '=', 'proyecto.id_proyecto')
                ->where('asignacion.id_trabajador', $trab->id_trabajador)
                ->orderByDesc('asignacion.fecha_asignacion')
                ->first();
        }

        // ---------- ASISTENCIA (HORAS TRABAJADAS) ----------
        $asistencias = [];
        $anioMes     = null;

        if ($trab) {
            $hoy        = Carbon::today();
            $inicioMes  = $hoy->copy()->startOfMonth();
            $finMes     = $hoy->copy()->endOfMonth();
            $anioMes    = $hoy->format('Y-m'); // ej: 2025-11

            $asistencias = DB::table('asistencia_trabajador')
                ->where('id_trabajador', $trab->id_trabajador)
                ->whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
                ->select('fecha', 'horas', 'pagado')
                ->orderBy('fecha')
                ->get();
        }

        // ---------- PAGOS RECIBIDOS POR EL TRABAJADOR ----------
        $pagosRecibidos = collect();
        $qrTrabajador   = null;

        if ($trab) {
            $qrTrabajador = $trab->codigoqr_trab; // lo mandamos tal cual; en el front lo muestras como <img>

            $pagosRecibidos = DB::table('pago as pa')
                ->leftJoin('plan_cuota as pc', 'pa.id_cuota', '=', 'pc.id_cuota')
                ->where('pa.id_trabajador', $trab->id_trabajador)
                ->orderByDesc('pa.fecha_pago')
                ->select(
                    'pa.id_pago',
                    'pa.fecha_pago',
                    'pa.monto_total_pago',
                    'pa.metodo_pago',
                    'pa.estado_pago',
                    'pa.cuotas_pago',
                    'pa.id_cuota',
                    'pa.concepto_pago',
                    DB::raw("
                        CASE
                            WHEN (pa.cuotas_pago IS NULL OR pa.cuotas_pago = 0)
                                 AND pa.id_cuota IS NULL
                                THEN 'Pago directo'
                            WHEN pa.id_cuota IS NOT NULL
                                THEN CONCAT('Cuota #', COALESCE(pc.numero_cuota, 1))
                            ELSE 'Plan en cuotas'
                        END AS tipo_detalle
                    ")
                )
                ->get();
        }

        return Inertia::render('Trabajador/Dashboard', [
            'user'             => $u,
            'proyectoAsignado' => $proyectoAsignado,
            'asistencias'      => $asistencias,
            'mesActual'        => $anioMes,
            'pagosRecibidos'   => $pagosRecibidos, // para la tabla en Vue
            'qrTrabajador'     => $qrTrabajador,   // para mostrar el QR grande
        ]);
    }

    /**
     * (Opcional) Endpoint JSON por si quieres recargar la tabla de pagos con Axios.
     */
    public function pagosRecibidos()
    {
        /** @var Usuario $u */
        $u = Auth::user();

        if (! $u || $u->role !== 'trabajador') {
            return response()->json([
                'ok'      => false,
                'message' => 'No autorizado.',
            ], 403);
        }

        $u->load('trabajador');
        $trab = $u->trabajador;

        if (! $trab) {
            return response()->json([
                'ok'      => false,
                'message' => 'No se encontró el trabajador asociado.',
            ], 403);
        }

        $pagos = DB::table('pago as pa')
            ->leftJoin('plan_cuota as pc', 'pa.id_cuota', '=', 'pc.id_cuota')
            ->where('pa.id_trabajador', $trab->id_trabajador)
            ->orderByDesc('pa.fecha_pago')
            ->select(
                'pa.id_pago',
                'pa.fecha_pago',
                'pa.monto_total_pago',
                'pa.metodo_pago',
                'pa.estado_pago',
                'pa.cuotas_pago',
                'pa.id_cuota',
                'pa.concepto_pago',
                DB::raw("
                    CASE
                        WHEN (pa.cuotas_pago IS NULL OR pa.cuotas_pago = 0)
                             AND pa.id_cuota IS NULL
                            THEN 'Pago directo'
                        WHEN pa.id_cuota IS NOT NULL
                            THEN CONCAT('Cuota #', COALESCE(pc.numero_cuota, 1))
                        ELSE 'Plan en cuotas'
                    END AS tipo_detalle
                ")
            )
            ->get();

        return response()->json([
            'ok'    => true,
            'pagos' => $pagos,
        ]);
    }

    /**
     * Subir / actualizar el código QR del trabajador.
     * Espera un input file llamado "qr".
     */
  public function actualizarQr(Request $request): JsonResponse
{
    /** @var Usuario $u */
    $u = Auth::user();

    if (! $u || $u->role !== 'trabajador') {
        return response()->json([
            'message' => 'No autorizado.',
        ], 403);
    }

    $request->validate([
        'qr' => 'required|image|max:4096', // 4 MB
    ]);

    // trabajador logueado
    $trab = Trabajador::where('id_usuario', $u->id_usuario)->first();

    if (! $trab) {
        return response()->json([
            'message' => 'No se encontró el trabajador asociado.',
        ], 404);
    }

    // borrar QR anterior si existe
    if ($trab->codigoqr_trab) {
        Storage::disk('public')->delete($trab->codigoqr_trab);
    }

    // guardar nuevo en: storage/app/public/trabajadores/qr
    $path = $request->file('qr')->store('trabajadores/qr', 'public');

    $trab->codigoqr_trab = $path;
    $trab->save();

    return response()->json([
        'ok'      => true,
        'message' => 'QR actualizado correctamente.',
        'path'    => $path,
    ]);
}

   public function cambiarPassword(Request $request)
{
    /** @var Usuario $u */
    $u = Auth::user();

    if (! $u || $u->role !== 'trabajador') {
        return response()->json([
            'message' => 'No autorizado.',
        ], 403);
    }

    // password_nueva_confirmation debe venir del front
    $request->validate([
        'password_actual' => 'required|string',
        'password_nueva'  => 'required|string|min:4|confirmed',
    ]);

    // Verificar contraseña actual usando el hash almacenado
    if (! Hash::check($request->password_actual, $u->contrasenia)) {
        return response()->json([
            'message' => 'La contraseña actual no es correcta.',
        ], 422);
    }

    // Asignar nueva contraseña (el mutator del modelo la hashea)
    $u->contrasenia = $request->password_nueva;
    $u->save();

    return response()->json([
        'ok'      => true,
        'message' => 'Contraseña actualizada correctamente.',
    ]);
}

}
