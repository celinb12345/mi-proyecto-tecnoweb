<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\PlanCuota;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PagoFacilService;
use Inertia\Inertia;

class PagoClienteController extends Controller
{
    protected PagoFacilService $pagoFacil;

    public function __construct(PagoFacilService $pagoFacil)
    {
        $this->pagoFacil = $pagoFacil;
    }

    /**
     * Página de pagos del cliente (Inertia: Cliente/PagosCliente.vue)
     */
    public function index()
    {
        $u = Auth::user();
        if (! $u) {
            return redirect()->route('login');
        }

        // 1) Cliente logueado
        $cliente = DB::table('cliente')
            ->where('id_usuario', $u->id_usuario)
            ->first();

        if (! $cliente) {
            abort(403, 'No se encontró información del cliente.');
        }

        // 2) Último proyecto del cliente
        $proyecto = DB::table('proyecto')
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('id_proyecto')
            ->first();

        $propietario = null;

        if ($proyecto) {
            // Propietario del proyecto
            $propietario = DB::table('propietario')
                ->where('id_propietario', $proyecto->id_propietario)
                ->first();

            // casteo a float para evitar problemas de tipo
            $proyecto->monto_total_pro = (float) ($proyecto->monto_total_pro ?? 0);
        }

        // 3) TOTAL PAGADO POR EL CLIENTE EN ESE PROYECTO (estado_pago = true)
        $totalPagado = Pago::where('id_cliente', $cliente->id_cliente)
            ->when($proyecto, function ($q) use ($proyecto) {
                $q->where('id_proyecto', $proyecto->id_proyecto);
            })
            ->where('estado_pago', true)
            ->sum('monto_total_pago');

        // 4) Pagos realizados
        $pagos = Pago::where('id_cliente', $cliente->id_cliente)
            ->when($proyecto, function ($q) use ($proyecto) {
                $q->where('id_proyecto', $proyecto->id_proyecto);
            })
            ->orderByDesc('fecha_pago')
            ->get();

        return Inertia::render('Cliente/PagosCliente', [
            'proyecto'    => $proyecto,
            'propietario' => $propietario,
            'totalPagado' => (float) $totalPagado,
            'pagos'       => $pagos,
        ]);
    }

    /**
     * Registrar pago del cliente (efectivo o QR, directo o en cuotas).
     */
    public function store(Request $request)
    {
        /** @var \App\Models\Usuario $u */
        $u = Auth::user();

        if (! $u || $u->role !== 'cliente') {
            return response()->json([
                'ok'      => false,
                'message' => 'No autorizado.',
            ], 403);
        }

        $u->load('cliente');
        $cliente = $u->cliente;

        if (! $cliente) {
            return response()->json([
                'ok'      => false,
                'message' => 'No se encontró el cliente asociado.',
            ], 403);
        }

        // Validación de datos del pago
        $data = $request->validate([
            'id_proyecto'        => 'required|integer|exists:proyecto,id_proyecto',
            'tipo_pago'          => 'required|string|in:cliente',
            'metodo_pago'        => 'required|string|in:efectivo,qr',
            'fecha_pago'         => 'required|date',
            'monto_total_pago'   => 'required|numeric|min:0.01',
            'numero_comprobante' => 'nullable|integer',
            'concepto_pago'      => 'nullable|string|max:100',

            'en_cuotas'          => 'required|boolean',
            'plan'               => 'required_if:en_cuotas,true|array',
            'plan.*.numero'      => 'nullable|integer',
            'plan.*.monto'       => 'nullable|numeric',
            'plan.*.fecha'       => 'nullable|date',
            'plan.*.estado'      => 'nullable|string',

            // pagar una cuota ya existente
            'id_cuota'           => 'nullable|integer|exists:plan_cuota,id_cuota',
        ]);

        try {
            DB::beginTransaction();

            $idPropietario = $cliente->id_propietario;
            $esCuotas      = (bool) $data['en_cuotas'];
            $plan          = $esCuotas ? ($data['plan'] ?? []) : [];
            $esQr          = $data['metodo_pago'] === 'qr';
            $idCuotaSel    = $data['id_cuota'] ?? null;

            // Monto real del pago: si hay plan de cuotas, usamos la 1ª cuota
            if ($esCuotas && !empty($plan) && isset($plan[0]['monto'])) {
                $montoPago = (float) $plan[0]['monto'];
            } else {
                $montoPago = (float) $data['monto_total_pago'];
            }

            // Estado inicial del pago: si es QR queda pendiente
            $estadoInicial = $esQr ? false : true;

            // 1) Crear registro en PAGO
            $pago = Pago::create([
                'id_propietario'        => $idPropietario,
                'tipo_pago'             => 'cliente',
                'monto_total_pago'      => $montoPago,
                'cuotas_pago'           => $esCuotas ? count($plan) : 0,
                'estado_pago'           => $estadoInicial,
                'fecha_pago'            => $data['fecha_pago'],
                'metodo_pago'           => $data['metodo_pago'],
                'numero_comprobante'    => $data['numero_comprobante'] ?? null,
                'concepto_pago'         => $data['concepto_pago'] ?? '',
                'id_cuota'              => $idCuotaSel,
                'id_proveedor'          => null,
                'id_trabajador'         => null,
                'id_cliente'            => $cliente->id_cliente,
                'transaccion_pagofacil' => null,
                'fecha_expira_qr'       => null,
            ]);

            // 2) Si se está creando un plan NUEVO de cuotas
            if ($esCuotas && !empty($plan)) {
                foreach ($plan as $i => $cuota) {
                    if (!isset($cuota['monto'], $cuota['fecha'], $cuota['numero'])) {
                        continue;
                    }

                    $esPrimera = $i === 0;

                    // primera cuota pagada solo si es EFECTIVO
                    $estadoCuota = (!$esQr && $esPrimera) ? true : false;

                    PlanCuota::create([
                        'id_propietario'    => $idPropietario,
                        'id_pago'           => $esPrimera ? $pago->id_pago : null,
                        'numero_cuota'      => $cuota['numero'],
                        'monto_cuota'       => $cuota['monto'],
                        'fecha_vencimiento' => $cuota['fecha'],
                        'estado_cuota'      => $estadoCuota,
                        'id_proyecto'       => $data['id_proyecto'],
                    ]);
                }
            }

            // 3) Si se está pagando una cuota ya existente desde la tabla
            if ($idCuotaSel) {
                $cuota = PlanCuota::where('id_cuota', $idCuotaSel)->first();

                if ($cuota) {
                    $cuota->id_pago = $pago->id_pago;

                    // si NO es QR, se marca como pagada de inmediato
                    if (! $esQr) {
                        $cuota->estado_cuota = true;
                    }

                    $cuota->save();
                }
            }

            // =====================================================
            // 4) SI ES QR → GENERAR QR EN PAGO FÁCIL (SOLO CLIENTE)
            // =====================================================
            if ($esQr) {
                $clientName = trim(($cliente->nombre_cli ?? '') . ' ' . ($cliente->apellido_cli ?? ''));
                if ($clientName === '') {
                    $clientName = 'Cliente proyecto';
                }

                $email = $cliente->correo_cli ?? ($u->email ?? 'sin-correo@example.com');
                $phone = $cliente->telefono_cli ?? '0';

                try {
                    $json = $this->pagoFacil->generarQrCliente([
                        'paymentNumber' => (string) $pago->id_pago,
                        'amount'        => $montoPago,
                        'clientName'    => $clientName,
                        'email'         => $email,
                        'phone'         => $phone,
                        'clientCode'    => $cliente->id_cliente,
                        'concepto'      => $data['concepto_pago'] ?? 'Pago de proyecto',
                    ]);
                } catch (\Throwable $e) {
                    DB::rollBack();

                    Log::error('Error al generar QR PagoFácil (cliente)', [
                        'error' => $e->getMessage(),
                    ]);

                    return response()->json([
                        'ok'      => false,
                        'message' => 'No se pudo generar el QR en PagoFácil. Intente nuevamente.',
                    ], 500);
                }

                $values = $json['values'] ?? [];

                $pago->update([
                    'transaccion_pagofacil' => $values['transactionId']  ?? null,
                    'fecha_expira_qr'       => $values['expirationDate'] ?? null,
                ]);

                DB::commit();

                return response()->json([
                    'ok'          => true,
                    'message'     => $json['message'] ?? 'QR generado correctamente. Escanee el código para pagar.',
                    'pago'        => $pago->fresh(),
                    'qr_image'    => $values['qrBase64']      ?? null,
                    'transaction' => $values['transactionId'] ?? null,
                    'checkoutUrl' => $values['checkoutUrl']   ?? null,
                    'deepLink'    => $values['deepLink']      ?? null,
                    'universalUrl'=> $values['universalUrl']  ?? null,
                ], 201);
            }

            // =====================================================
            // 5) PAGO NORMAL (EFECTIVO)
            // =====================================================
            DB::commit();

            return response()->json([
                'ok'      => true,
                'message' => 'Pago registrado correctamente.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Error al registrar pago de cliente', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'Error al registrar el pago.',
            ], 500);
        }
    }

    /**
     * Consultar estado de un pago QR en PagoFácil (cliente).
     */
    public function consultar(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required',
        ]);

        try {
            $codigo = (string) $data['codigo'];

            $json = $this->pagoFacil->consultarTransaccion($codigo);

            if (array_key_exists('error', $json) && $json['error'] !== 0) {
                return response()->json([
                    'ok'      => false,
                    'message' => $json['message'] ?? 'Error al consultar estado en PagoFácil.',
                    'json'    => $json,
                ], 500);
            }

            $values = $json['values'] ?? [];

            $paymentStatus       = $values['paymentStatus']            ?? null;
            $paymentStatusDesc   = $values['paymentStatusDescription'] ?? null;
            $paymentStatusDescLc = strtolower((string) $paymentStatusDesc);

            $rawState = $paymentStatusDesc
                ?: ($paymentStatus !== null ? (string) $paymentStatus : '');

            $isPaid =
                ((int) $paymentStatus === 2) ||
                str_contains($paymentStatusDescLc, 'pagado') ||
                str_contains($paymentStatusDescLc, 'aprob')  ||
                str_contains($paymentStatusDescLc, 'complet');

            $pago = Pago::where('transaccion_pagofacil', $codigo)
                ->orWhere('id_pago', $codigo)
                ->first();

            if ($isPaid && $pago && !$pago->estado_pago) {
                $pago->estado_pago = true;
                $pago->save();

                // Si es pago de cliente, marcar primera cuota asociada como pagada
                if ($pago->tipo_pago === 'cliente') {
                    $cuota = PlanCuota::where('id_pago', $pago->id_pago)
                        ->orderBy('numero_cuota')
                        ->first();

                    if ($cuota && !$cuota->estado_cuota) {
                        $cuota->estado_cuota = true;
                        $cuota->save();
                    }
                }
            }

            return response()->json([
                'ok'        => true,
                'message'   => 'Consulta realizada correctamente.',
                'resultado' => $values,
                'estado'    => $rawState,
                'isPaid'    => $isPaid,
                'pago'      => $pago,
            ]);
        } catch (\Throwable $e) {
            Log::error('PagosCliente@consultar: '.$e->getMessage(), [
                'line'  => $e->getLine(),
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'Ocurrió un error al consultar el estado del pago.',
            ], 500);
        }
    }

    /**
     * Callback que llamará PagoFácil para pagos de clientes.
     */
    public function callbackPagofacil(Request $request)
    {
        Log::info('Callback PagoFácil CLIENTE', [
            'query' => $request->query(),
            'body'  => $request->all(),
        ]);

        $pedidoId = $request->input('id_pago')
            ?? $request->input('paymentNumber')
            ?? $request->input('pagofacilTransactionId');

        if (!$pedidoId) {
            return response()->json([
                'error'   => 1,
                'status'  => 400,
                'message' => 'No se envió ningún identificador de pago.',
                'values'  => false,
            ], 400);
        }

        // Busca el pago por id_pago o por transaccion_pagofacil
        $pago = Pago::where('id_pago', $pedidoId)
            ->orWhere('transaccion_pagofacil', $pedidoId)
            ->first();

        if ($pago) {
            $pago->estado_pago = true;
            $pago->save();

            // Marcar la primera cuota asociada como pagada (si aplica)
            if ($pago->tipo_pago === 'cliente') {
                $cuota = PlanCuota::where('id_pago', $pago->id_pago)
                    ->orderBy('numero_cuota')
                    ->first();

                if ($cuota && !$cuota->estado_cuota) {
                    $cuota->estado_cuota = true;
                    $cuota->save();
                }
            }
        }

        return response()->json([
            'error'   => 0,
            'status'  => 1,
            'message' => 'Notificación recibida correctamente.',
            'values'  => true,
        ]);
    }

    /**
     * Cuotas pendientes del cliente (JOIN proyecto + plan_cuota).
     */
    public function cuotasPendientes()
    {
        /** @var \App\Models\Usuario $u */
        $u = Auth::user();

        if (! $u || $u->role !== 'cliente') {
            return response()->json([
                'ok'      => false,
                'message' => 'No autorizado.',
            ], 403);
        }

        $u->load('cliente');
        $cliente = $u->cliente;

        if (! $cliente) {
            return response()->json([
                'ok'      => false,
                'message' => 'No se encontró el cliente asociado.',
            ], 403);
        }

        // cuotas pendientes de proyectos de este cliente
        $cuotas = DB::table('plan_cuota as pc')
            ->join('proyecto as p', 'pc.id_proyecto', '=', 'p.id_proyecto')
            ->where('p.id_cliente', $cliente->id_cliente)
            ->where('pc.estado_cuota', false)
            ->orderBy('pc.fecha_vencimiento')
            ->select(
                'pc.id_cuota',
                'pc.numero_cuota',
                'pc.monto_cuota',
                'pc.fecha_vencimiento'
            )
            ->get();

        return response()->json([
            'ok'     => true,
            'cuotas' => $cuotas,
        ]);
    }
}
