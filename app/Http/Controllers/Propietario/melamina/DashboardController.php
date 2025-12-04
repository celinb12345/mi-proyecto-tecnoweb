<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Id del propietario logueado.
     */
    protected function propietarioId()
    {
        // Si tu relación es Usuario -> propietario
        return Auth::user()->propietario->id_propietario;
        // Si luego usas usuario.id_propietario, solo cambias aquí.
    }

    /**
     * 1) RESUMEN PERSONAS (trabajadores / clientes / proveedores)
     *    Gráfico de torta.
     * GET /propietario/melamina/dashboard/resumen-personas
     */
    public function resumenPersonas()
    {
        $idProp = $this->propietarioId();

        $trabajadores = DB::table('trabajador')
            ->where('id_propietario', $idProp)
            ->count();

        $clientes = DB::table('cliente')
            ->where('id_propietario', $idProp)
            ->count();

        $proveedores = DB::table('proveedor')
            ->where('id_propietario', $idProp)
            ->count();

        $labels = ['Trabajadores', 'Clientes', 'Proveedores'];
        $cantidades = [$trabajadores, $clientes, $proveedores];

        $total = array_sum($cantidades);
        if ($total <= 0) {
            $porcentajes = [0, 0, 0];
        } else {
            $porcentajes = array_map(function ($v) use ($total) {
                return round(($v / $total) * 100, 1);
            }, $cantidades);
        }

        return response()->json([
            'labels'      => $labels,
            'cantidades'  => $cantidades,
            'porcentajes' => $porcentajes,
            'total'       => $total,
        ]);
    }

    /**
     * 2) INVENTARIO: ENTRADAS vs SALIDAS POR MES (AÑO-MES)
     *    Gráfico de barras.
     * GET /propietario/melamina/dashboard/inventario-mensual
     */
    public function inventarioMensual()
    {
        $idProp = $this->propietarioId();

        $rows = DB::table('inventario')
            ->where('id_propietario', $idProp)
            ->selectRaw("
                TO_CHAR(fecha_inv, 'YYYY-MM') as periodo,
                SUM(CASE WHEN tipo_movimiento = TRUE  THEN cantidad_inv ELSE 0 END) as entradas,
                SUM(CASE WHEN tipo_movimiento = FALSE THEN cantidad_inv ELSE 0 END) as salidas
            ")
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->limit(12)   // últimos 12 meses; puedes quitarlo si quieres todos
            ->get();

        return response()->json([
            'labels'   => $rows->pluck('periodo'),
            'entradas' => $rows->pluck('entradas')->map(fn($v) => (float)$v),
            'salidas'  => $rows->pluck('salidas')->map(fn($v) => (float)$v),
        ]);
    }

    /**
     * 3) PAGOS PENDIENTES (trabajador / proveedor / cliente)
     *    Gráfico de barras.
     * GET /propietario/melamina/dashboard/pagos-pendientes
     */
    public function pagosPendientes()
    {
        $idProp = $this->propietarioId();

        // estado_pago = FALSE => pendiente
        $trabajadores = DB::table('pago')
            ->where('id_propietario', $idProp)
            ->where('estado_pago', false)
            ->whereNotNull('id_trabajador')
            ->sum('monto_total_pago');

        $proveedores = DB::table('pago')
            ->where('id_propietario', $idProp)
            ->where('estado_pago', false)
            ->whereNotNull('id_proveedor')
            ->sum('monto_total_pago');

        $clientes = DB::table('pago')
            ->where('id_propietario', $idProp)
            ->where('estado_pago', false)
            ->whereNotNull('id_cliente')
            ->sum('monto_total_pago');

        $labels = ['Trabajadores', 'Proveedores', 'Clientes'];
        $montos = [
            (float)$trabajadores,
            (float)$proveedores,
            (float)$clientes,
        ];

        return response()->json([
            'labels'      => $labels,
            'montos'      => $montos,
            'total_monto' => array_sum($montos),
        ]);
    }

    /**
     * 4) PROYECTOS: CUENTA REGRESIVA (días restantes)
     *    Gráfico de barras horizontal pequeño.
     * GET /propietario/melamina/dashboard/proyectos-contador
     */
    public function proyectosContador()
    {
        $idProp = $this->propietarioId();
        $hoy = Carbon::today();

        $rows = DB::table('proyecto')
            ->where('id_propietario', $idProp)
            ->orderBy('fecha_fin_pro')
            ->get();

        $labels = [];
        $diasRestantes = [];
        $detalles = [];

        foreach ($rows as $proyecto) {
            $inicio = $proyecto->fecha_inicio_pro
                ? Carbon::parse($proyecto->fecha_inicio_pro)
                : null;
            $fin = $proyecto->fecha_fin_pro
                ? Carbon::parse($proyecto->fecha_fin_pro)
                : null;

            if ($fin) {
                $totalDias = max(1, $inicio ? $inicio->diffInDays($fin) : $hoy->diffInDays($fin));
                $pasados = $inicio ? $inicio->diffInDays(min($hoy, $fin)) : 0;
                $restantes = max(0, $hoy->diffInDays($fin, false)); // 0 si ya pasó
            } else {
                // sin fecha fin: lo consideramos 0 días restantes
                $totalDias = null;
                $pasados = null;
                $restantes = 0;
            }

            $labels[] = $proyecto->nombre_pro ?? 'Proyecto #' . $proyecto->id_proyecto;
            $diasRestantes[] = (int)$restantes;

            $detalles[] = [
                'nombre'          => $proyecto->nombre_pro,
                'estado'          => $proyecto->estado_proyecto,
                'fecha_inicio'    => $proyecto->fecha_inicio_pro,
                'fecha_fin'       => $proyecto->fecha_fin_pro,
                'dias_restantes'  => $restantes,
                'total_dias'      => $totalDias,
            ];
        }

        return response()->json([
            'labels'         => $labels,
            'dias_restantes' => $diasRestantes,
            'detalles'       => $detalles,
        ]);
    }

    /**
 * 5) CUOTAS PENDIENTES (trabajador / proveedor / cliente)
 *    GET /propietario/melamina/dashboard/cuotas-pendientes
 */
public function cuotasPendientesGeneral()
{
    $idProp = $this->propietarioId();

    $row = DB::table('plan_cuota as pc')
        ->join('pago as pa', 'pc.id_pago', '=', 'pa.id_pago')
        ->where('pc.id_propietario', $idProp)
        ->where('pc.estado_cuota', false) // solo cuotas pendientes
        ->selectRaw("
            -- CANTIDADES
            SUM(CASE WHEN pa.id_trabajador IS NOT NULL THEN 1 ELSE 0 END) AS cant_trab,
            SUM(CASE WHEN pa.id_proveedor  IS NOT NULL THEN 1 ELSE 0 END) AS cant_prov,
            SUM(CASE WHEN pa.id_cliente    IS NOT NULL THEN 1 ELSE 0 END) AS cant_cli,

            -- MONTOS
            SUM(CASE WHEN pa.id_trabajador IS NOT NULL THEN pc.monto_cuota ELSE 0 END) AS monto_trab,
            SUM(CASE WHEN pa.id_proveedor  IS NOT NULL THEN pc.monto_cuota ELSE 0 END) AS monto_prov,
            SUM(CASE WHEN pa.id_cliente    IS NOT NULL THEN pc.monto_cuota ELSE 0 END) AS monto_cli
        ")
        ->first();

    $labels = ['Trabajadores', 'Proveedores', 'Clientes'];

    $cantidades = [
        (int) ($row->cant_trab  ?? 0),
        (int) ($row->cant_prov  ?? 0),
        (int) ($row->cant_cli   ?? 0),
    ];

    $montos = [
        (float) ($row->monto_trab ?? 0),
        (float) ($row->monto_prov ?? 0),
        (float) ($row->monto_cli  ?? 0),
    ];

    return response()->json([
        'labels'       => $labels,
        'cantidades'   => $cantidades,
        'montos'       => $montos,
        'total_cuotas' => array_sum($cantidades),
        'total_monto'  => array_sum($montos),
    ]);
}

public function proyectosPagosClientes()
{
    $idProp = $this->propietarioId();

    // 1) Resumen por proyecto (para la gráfica)
    $resumen = DB::table('plan_cuota as pc')
        ->join('proyecto as pr', 'pc.id_proyecto', '=', 'pr.id_proyecto')
        ->where('pc.id_propietario', $idProp)
        ->whereNotNull('pc.id_proyecto')
        ->selectRaw("
            pr.id_proyecto,
            pr.nombre_pro,
            COUNT(pc.id_cuota)                            AS total_cuotas,
            SUM(pc.monto_cuota)                           AS precio_total,
            SUM(CASE WHEN pc.estado_cuota = TRUE 
                     THEN pc.monto_cuota ELSE 0 END)      AS monto_pagado
        ")
        ->groupBy('pr.id_proyecto', 'pr.nombre_pro')
        ->orderBy('pr.nombre_pro')
        ->get();

    $labels        = [];
    $cuotasTotales = [];
    $preciosTot    = [];
    $montosPagados = [];

    $mapProyectos = []; // para usar después al armar el detalle

    foreach ($resumen as $row) {
        $labels[]        = $row->nombre_pro;
        $cuotasTotales[] = (int) $row->total_cuotas;
        $preciosTot[]    = (float) $row->precio_total;
        $montosPagados[] = (float) $row->monto_pagado;

        $mapProyectos[$row->id_proyecto] = [
            'id_proyecto'   => $row->id_proyecto,
            'nombre'        => $row->nombre_pro,
            'total_cuotas'  => (int) $row->total_cuotas,
            'precio_total'  => (float) $row->precio_total,
            'monto_pagado'  => (float) $row->monto_pagado,
            'cuotas'        => [],
        ];
    }

    // 2) Detalle de las cuotas por proyecto (número, monto, fecha, estado)
    $cuotas = DB::table('plan_cuota as pc')
        ->join('proyecto as pr', 'pc.id_proyecto', '=', 'pr.id_proyecto')
        ->where('pc.id_propietario', $idProp)
        ->whereNotNull('pc.id_proyecto')
        ->select(
            'pr.id_proyecto',
            'pc.numero_cuota',
            'pc.monto_cuota',
            'pc.fecha_vencimiento',
            'pc.estado_cuota'
        )
        ->orderBy('pr.id_proyecto')
        ->orderBy('pc.numero_cuota')
        ->get();

    foreach ($cuotas as $c) {
        if (!isset($mapProyectos[$c->id_proyecto])) {
            continue; // por seguridad
        }

        $mapProyectos[$c->id_proyecto]['cuotas'][] = [
            'numero_cuota'      => (int) $c->numero_cuota,
            'monto_cuota'       => (float) $c->monto_cuota,
            'fecha_vencimiento' => $c->fecha_vencimiento,
            'estado_cuota'      => (bool) $c->estado_cuota,
        ];
    }

    return response()->json([
        'labels'         => $labels,
        'cuotas_totales' => $cuotasTotales,
        'precios_totales'=> $preciosTot,
        'montos_pagados' => $montosPagados,
        'detalles'       => array_values($mapProyectos), // lista de proyectos con sus cuotas
    ]);
}

}
