<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pago;

class ReportePagoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // debe estar logueado y tener propietario
        if (! $user || ! $user->propietario) {
            return redirect()->route('login');
        }

        $idPropietario = $user->propietario->id_propietario;

        // base: solo pagos de este propietario
        $query = Pago::where('id_propietario', $idPropietario);

        // ========= FILTROS =========

        // 1) Tipo de destinatario (proveedor / trabajador / ambos)
        // en tu BD tienes columna tipo_pago (ej: 'proveedor','trabajador','cliente', etc.)
        if ($request->filled('sujeto') && $request->sujeto !== 'todos') {
            $sujeto = strtolower($request->sujeto); // proveedor | trabajador
            $query->where('tipo_pago', $sujeto);
        } else {
            // por si quieres excluir clientes en este reporte:
            $query->whereIn('tipo_pago', ['proveedor', 'trabajador']);
        }

        // 2) Método de pago: efectivo / qr
        if ($request->filled('metodo')) {
            $metodo = strtolower($request->metodo); // 'efectivo' | 'qr'
            $query->whereRaw('LOWER(metodo_pago) = ?', [$metodo]);
        }

        // 3) Forma de pago: directo / cuotas
        // directo => cuotas_pago NULL o 0
        // cuotas  => cuotas_pago > 0
        if ($request->filled('forma')) {
            if ($request->forma === 'directo') {
                $query->where(function ($q) {
                    $q->whereNull('cuotas_pago')
                      ->orWhere('cuotas_pago', 0);
                });
            } elseif ($request->forma === 'cuotas') {
                $query->where('cuotas_pago', '>', 0);
            }
        }

        // 4) Rango de fechas (fecha_pago)
        if ($request->filled('desde')) {
            $query->whereDate('fecha_pago', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha_pago', '<=', $request->hasta);
        }

        // ========= EJECUTAR CONSULTA =========
        $pagos = $query
            ->orderByDesc('fecha_pago')
            ->get([
                'id_pago',
                'id_propietario',
                'tipo_pago',          // proveedor / trabajador / cliente
                'monto_total_pago',
                'cuotas_pago',
                'estado_pago',
                'fecha_pago',
                'metodo_pago',
                'numero_comprobante',
                'concepto_pago',
                'id_proveedor',
                'id_trabajador',
            ]);

        // ========= RESUMEN PARA EL REPORTE =========
        $totalGeneral = $pagos->sum('monto_total_pago');

        $totalProveedores = $pagos
            ->where('tipo_pago', 'proveedor')
            ->sum('monto_total_pago');

        $totalTrabajadores = $pagos
            ->where('tipo_pago', 'trabajador')
            ->sum('monto_total_pago');

        $totalEfectivo = $pagos
            ->filter(fn ($p) => strtolower($p->metodo_pago ?? '') === 'efectivo')
            ->sum('monto_total_pago');

        $totalQr = $pagos
            ->filter(fn ($p) => strtolower($p->metodo_pago ?? '') === 'qr')
            ->sum('monto_total_pago');

        $totalDirecto = $pagos
            ->filter(fn ($p) => (int) ($p->cuotas_pago ?? 0) === 0)
            ->sum('monto_total_pago');

        $totalEnCuotas = $pagos
            ->filter(fn ($p) => (int) ($p->cuotas_pago ?? 0) > 0)
            ->sum('monto_total_pago');

        $resumen = [
            'total_general'      => $totalGeneral,
            'total_proveedores'  => $totalProveedores,
            'total_trabajadores' => $totalTrabajadores,
            'total_efectivo'     => $totalEfectivo,
            'total_qr'           => $totalQr,
            'total_directo'      => $totalDirecto,
            'total_en_cuotas'    => $totalEnCuotas,
        ];

        // Si la petición es AJAX/JSON (para filtros dinámicos)
        if ($request->wantsJson()) {
            return response()->json([
                'pagos'   => $pagos,
                'resumen' => $resumen,
            ]);
        }

        // Si entra por página normal Inertia
        return inertia('Propietario/Melamina/ReportePagos', [
            'pagos'   => $pagos,
            'resumen' => $resumen,
            // filtros actuales para que el front los mantenga marcados
            'filtros' => [
                'sujeto' => $request->input('sujeto', 'todos'),
                'metodo' => $request->input('metodo', null),
                'forma'  => $request->input('forma', null),
                'desde'  => $request->input('desde', null),
                'hasta'  => $request->input('hasta', null),
            ],
        ]);
    }
}
