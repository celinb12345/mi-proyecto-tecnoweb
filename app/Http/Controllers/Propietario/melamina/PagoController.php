<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Pago;
use App\Models\Trabajador;
use App\Models\Proveedor;
use App\Models\PlanCuota;

class PagoController extends Controller
{
    // ===========================
    // LISTAR PAGOS
    // ===========================
    public function index(Request $request)
    {
        try {
            $q = Pago::paraPropietario()->orderByDesc('fecha_pago');

            if ($request->filled('desde')) {
                $q->whereDate('fecha_pago', '>=', $request->input('desde'));
            }
            if ($request->filled('hasta')) {
                $q->whereDate('fecha_pago', '<=', $request->input('hasta'));
            }

            $pagos = $q->get();

            // Nombres de trabajador / proveedor
            $trabajadores = Trabajador::whereIn(
                'id_trabajador',
                $pagos->pluck('id_trabajador')->filter()->unique()
            )->get()->keyBy('id_trabajador');

            $proveedores = Proveedor::whereIn(
                'id_proveedor',
                $pagos->pluck('id_proveedor')->filter()->unique()
            )->get()->keyBy('id_proveedor');

            $pagos = $pagos->map(function ($p) use ($trabajadores, $proveedores) {
                $pArr = $p->toArray();

                $pArr['trabajador'] =
                    $p->id_trabajador && isset($trabajadores[$p->id_trabajador])
                        ? $trabajadores[$p->id_trabajador]
                        : null;

                $pArr['proveedor'] =
                    $p->id_proveedor && isset($proveedores[$p->id_proveedor])
                        ? $proveedores[$p->id_proveedor]
                        : null;

                return $pArr;
            });

            return response()->json([
                'ok'    => true,
                'pagos' => $pagos,
            ]);
        } catch (\Throwable $e) {
            Log::error("Pagos@index: {$e->getMessage()}");

            return response()->json([
                'ok'      => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ===========================
    // REGISTRAR PAGO
    // ===========================
    public function store(Request $request)
    {
        try {
            $input = $request->all();

            // Normalizar método de pago
            $input['metodo_pago'] = strtolower($input['metodo_pago'] ?? '');

            // Validación básica
            $base = validator($input, [
                'tipo_pago'          => 'required|in:proveedor,trabajador',
                'metodo_pago'        => 'required|in:efectivo,qr',
                'estado_pago'        => 'required',
                'monto_total_pago'   => 'required|numeric|min:0.01',
                'fecha_pago'         => 'required|date',
                'concepto_pago'      => 'nullable|string',
                'numero_comprobante' => 'nullable|integer',

                'id_trabajador'      => 'nullable|exists:trabajador,id_trabajador',
                'id_proveedor'       => 'nullable|exists:proveedor,id_proveedor',

                'en_cuotas'          => 'sometimes|boolean',
                'plan'               => 'sometimes|array',

                // pago de una cuota ya existente
                'id_cuota_origen'    => 'nullable|exists:plan_cuota,id_cuota',
            ])->validate();

            $idPropietario = Auth::user()->propietario->id_propietario;

            $esTrabajador   = $base['tipo_pago'] === 'trabajador';
            $esProveedor    = $base['tipo_pago'] === 'proveedor';
            $idCuotaOrigen  = $input['id_cuota_origen'] ?? null;

            if ($esTrabajador && empty($base['id_trabajador'])) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Debe seleccionar el trabajador.',
                ], 422);
            }

            if ($esProveedor && empty($base['id_proveedor'])) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Debe seleccionar el proveedor.',
                ], 422);
            }

            // --------- cuotas ----------
            $enCuotas = (bool)($input['en_cuotas'] ?? false);
            $plan     = is_array($input['plan'] ?? null) ? $input['plan'] : [];

            // para nuevo plan: el front manda TODAS las cuotas, pero la primera se paga ahora
            // => sólo guardamos como pendientes las cuotas 2..N
            $pendingPlan  = ($enCuotas && count($plan) > 1) ? array_slice($plan, 1) : [];
            $cuotasCount  = $enCuotas ? count($pendingPlan) : 0;

            // monto que se guardará en la tabla PAGO:
            // - si es plan nuevo: monto de la primera cuota
            // - si es pago directo / pago de cuota existente: el enviado en el formulario
            if ($enCuotas && !empty($plan)) {
                $montoPago = (float)($plan[0]['monto'] ?? $base['monto_total_pago']);
            } else {
                $montoPago = (float)$base['monto_total_pago'];
            }

            // Estado del pago: respeta lo elegido en el formulario,
            // tanto para efectivo como para QR.
            $estado = strtolower($base['estado_pago']) === 'pagado';

            DB::beginTransaction();

            // 1) Crear pago en BD
            $pago = Pago::create([
                'id_propietario'        => $idPropietario,
                'tipo_pago'             => $base['tipo_pago'],
                'monto_total_pago'      => $montoPago,
                'cuotas_pago'           => $enCuotas ? $cuotasCount : 0,
                'estado_pago'           => $estado,
                'fecha_pago'            => $base['fecha_pago'],
                'metodo_pago'           => $base['metodo_pago'],     // 'efectivo' o 'qr'
                'numero_comprobante'    => $base['numero_comprobante'] ?? null,
                'concepto_pago'         => $base['concepto_pago'] ?? null,
                'id_cuota'              => null,
                'id_proveedor'          => $esProveedor  ? $base['id_proveedor']  : null,
                'id_trabajador'         => $esTrabajador ? $base['id_trabajador'] : null,
                'id_cliente'            => null,
                'transaccion_pagofacil' => null,
                'fecha_expira_qr'       => null,
            ]);

            // 2) Si es NUEVO plan de cuotas -> guardar SOLO las cuotas pendientes (2..N)
            if ($enCuotas && $cuotasCount > 0) {
                foreach ($pendingPlan as $i => $cuota) {
                    if (empty($cuota['monto']) || empty($cuota['fecha'])) {
                        continue;
                    }

                    PlanCuota::create([
                        'id_propietario'    => $idPropietario,
                        'id_pago'           => $pago->id_pago,
                        'numero_cuota'      => $cuota['numero'] ?? ($i + 2), // +2 porque empezamos en la 2ª
                        'monto_cuota'       => $cuota['monto'],
                        'fecha_vencimiento' => $cuota['fecha'],
                        'estado_cuota'      => false, // pendiente
                        'id_proyecto'       => null,
                    ]);
                }
            }

            // 3) Si es pago de una cuota YA EXISTENTE (desde la tabla "Cuotas pendientes")
            if ($idCuotaOrigen) {
                PlanCuota::where('id_propietario', $idPropietario)
                    ->where('id_cuota', $idCuotaOrigen)
                    ->update(['estado_cuota' => true]); // la marcamos como pagada

                $pago->id_cuota = $idCuotaOrigen;
                $pago->save();
            }

            // 4) Registrar ASISTENCIA si es trabajador y se enviaron días
            if ($esTrabajador) {
                $detalleDias = $input['detalle_dias'] ?? [];

                if (is_array($detalleDias) && count($detalleDias) > 0) {
                    $registros = [];
                    // cada día = 8 horas de trabajo
                    $horasPorDia = 8;

                    foreach ($detalleDias as $fechaDia) {
                        if (!$fechaDia) {
                            continue;
                        }

                        $registros[] = [
                            'id_propietario' => $idPropietario,
                            'id_trabajador'  => $base['id_trabajador'],
                            'fecha'          => $fechaDia,
                            'horas'          => $horasPorDia,
                            'pagado'         => $estado,          // true si el pago se marcó como pagado
                            'id_pago'        => $pago->id_pago,
                        ];
                    }

                    if (!empty($registros)) {
                        DB::table('asistencia_trabajador')->insert($registros);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'ok'      => true,
                'message' => 'Pago registrado correctamente.',
                'pago'    => $pago,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Pagos@store: '.$e->getMessage(), [
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'Ocurrió un error al registrar el pago.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ===========================
    // CUOTAS PENDIENTES (PLAN_CUOTA)
    // ===========================
    public function cuotasPendientes()
    {
        $user = Auth::user();

        if (!$user || !$user->propietario) {
            return response()->json([
                'ok'      => false,
                'message' => 'No se encontró propietario autenticado.',
            ], 401);
        }

        $idPropietario = $user->propietario->id_propietario;

        $cuotas = DB::table('plan_cuota as pc')
            ->join('pago as pa', 'pc.id_pago', '=', 'pa.id_pago')
            ->leftJoin('trabajador as t', 'pa.id_trabajador', '=', 't.id_trabajador')
            ->leftJoin('proveedor as pr', 'pa.id_proveedor', '=', 'pr.id_proveedor')
            ->select(
                'pc.id_cuota',
                'pc.numero_cuota',
                'pc.monto_cuota',
                'pc.fecha_vencimiento',
                'pc.estado_cuota',
                'pc.id_proyecto',
                'pc.id_pago',

                'pa.tipo_pago',
                'pa.metodo_pago',
                'pa.concepto_pago',
                'pa.fecha_pago',
                'pa.id_trabajador',
                'pa.id_proveedor',

                DB::raw("
                    COALESCE(
                        NULLIF(TRIM(CONCAT(t.nombre_trabajador, ' ', t.apellido_trabajador)), ''),
                        NULLIF(TRIM(pr.nombre_empres_prov), ''),
                        'Sin destino'
                    ) AS destino
                ")
            )
            ->where('pc.id_propietario', $idPropietario)
            ->where('pc.estado_cuota', false)
            ->orderBy('pc.fecha_vencimiento')
            ->orderBy('pc.numero_cuota')
            ->get();

        return response()->json([
            'ok'     => true,
            'cuotas' => $cuotas,
        ]);
    }

    // ===========================
    // CATÁLOGOS
    // ===========================
    public function listarTrabajadores()
    {
        return Trabajador::paraPropietario()
            ->select(
                'id_trabajador',
                'nombre_trabajador',
                'apellido_trabajador',
                'sueldo_trabajador',
                'codigoqr_trab'
            )
            ->orderBy('nombre_trabajador')
            ->get();
    }

    public function listarProveedores()
    {
        return Proveedor::paraPropietario()
            ->select(
                'id_proveedor',
                'nombre_empres_prov',
                'codigoqr_prov'
            )
            ->orderBy('nombre_empres_prov')
            ->get();
    }
}
