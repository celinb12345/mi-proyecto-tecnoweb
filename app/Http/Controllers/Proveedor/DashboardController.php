<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
// Modelos
use App\Models\Usuario;
use App\Models\Propietario;
use App\Models\Proyecto;
use App\Models\Pago;
use Illuminate\Http\JsonResponse;
use App\Models\Proveedor;

class DashboardController extends Controller
{
    /**
     * Panel principal del proveedor
     */
    public function index(Request $request)
    {
        /** @var Usuario $u */
        $u = Auth::user();

        if (! $u) {
            return redirect()->route('login');
        }

        // Solo permite entrar a usuarios con rol proveedor
        if ($u->role !== 'proveedor') {
            Auth::logout();
            return redirect()->route('login');
        }

        // Cargar relación proveedor (debe estar definida en el modelo Usuario)
        $u->load('proveedor');
        $prov = $u->proveedor;

        $proyectoAsignado     = null;
        $propietarioContacto  = null;
        $pagosConCuotas       = [];
        $qrProveedor          = null;

        if ($prov) {
            // QR del proveedor (ruta guardada en BD, ej: "proveedores/qr/miqr.png")
            $qrProveedor = $prov->codigoqr_prov;

            // 🔹 Contacto del propietario enlazado a este proveedor
            if (!empty($prov->id_propietario)) {
                $propietarioContacto = Propietario::find($prov->id_propietario);
            }

            // 🔹 Proyecto para mostrar ubicación (último proyecto de ese propietario)
            if (!empty($prov->id_propietario)) {
                $proyectoAsignado = Proyecto::where('id_propietario', $prov->id_propietario)
                    ->orderByDesc('fecha_inicio_pro')
                    ->first();
            }

            // ---------- PAGOS + DETALLE DE CUOTAS ----------
            // 1) Pagos base del proveedor
            $pagosBase = Pago::where('id_proveedor', $prov->id_proveedor)
                ->orderByDesc('fecha_pago')
                ->get([
                    'id_pago',
                    'fecha_pago',
                    'monto_total_pago',
                    'estado_pago',
                    'metodo_pago',
                    'concepto_pago',
                    'cuotas_pago',    // si > 0 => en cuotas
                ]);

            // IDs de pagos que tienen cuotas
            $idsConCuotas = $pagosBase
                ->where('cuotas_pago', '>', 0)
                ->pluck('id_pago')
                ->all();

            // 2) Traer las cuotas de esos pagos (si hay)
            $cuotas = [];
            if (!empty($idsConCuotas)) {
                $cuotas = DB::table('plan_cuota')
                    ->whereIn('id_pago', $idsConCuotas)
                    ->orderBy('id_pago')
                    ->orderBy('numero_cuota')
                    ->get([
                        'id_pago',
                        'id_cuota',
                        'numero_cuota',
                        'monto_cuota',
                        'fecha_vencimiento',
                        'estado_cuota',
                    ]);
            }

            // Mapear cuotas por id_pago
            $mapCuotas = [];
            foreach ($cuotas as $c) {
                $mapCuotas[$c->id_pago][] = [
                    'id_cuota'          => $c->id_cuota,
                    'numero_cuota'      => (int) $c->numero_cuota,
                    'monto_cuota'       => (float) $c->monto_cuota,
                    'fecha_vencimiento' => $c->fecha_vencimiento,
                    'estado_cuota'      => (bool) $c->estado_cuota,
                ];
            }

            // 3) Armar estructura final de pagos + detalle de cuotas
            $pagosConCuotas = $pagosBase->map(function ($p) use ($mapCuotas) {
                $enCuotas = (int) ($p->cuotas_pago ?? 0) > 0;

                return [
                    'id_pago'          => $p->id_pago,
                    'fecha_pago'       => $p->fecha_pago,
                    'monto_total_pago' => (float) $p->monto_total_pago,
                    'estado_pago'      => (bool) $p->estado_pago,
                    'metodo_pago'      => $p->metodo_pago,
                    'concepto_pago'    => $p->concepto_pago,
                    'cuotas_pago'      => (int) ($p->cuotas_pago ?? 0),
                    'en_cuotas'        => $enCuotas,
                    'cuotas'           => $enCuotas
                        ? ($mapCuotas[$p->id_pago] ?? [])
                        : [],
                ];
            })->values();
        }

        return Inertia::render('Proveedor/Dashboard', [
            'user'               => $u,
            'proveedor'          => $prov,              // datos del proveedor logueado
            'proyectoAsignado'   => $proyectoAsignado,
            'pagos'              => $pagosConCuotas,    // pagos + detalle de cuotas
            'qrProveedor'        => $qrProveedor,       // ruta del QR en storage
            'propietarioContacto'=> $propietarioContacto,
        ]);
    }

    /**
     * Cambiar contraseña del proveedor (usuario logueado)
     */
     public function cambiarPassword(Request $request)
{
    /** @var Usuario $u */
    $u = Auth::user();

    if (! $u || $u->role !== 'proveedor') {
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
    public function actualizarQr(Request $request): JsonResponse
{
    /** @var Usuario|null $u */
    $u = Auth::user();

    // 1) Verificar que haya usuario autenticado
    if (! $u) {
        return response()->json([
            'message' => 'No autenticado.',
        ], 401);
    }

    // 2) Cargar relación proveedor y verificar que exista
    $u->load('proveedor');
    $prov = $u->proveedor;

    if (! $prov) {
        return response()->json([
            'message' => 'No se encontró el proveedor asociado al usuario.',
        ], 404);
    }

    // 3) Validar archivo
    $request->validate([
        'qr' => 'required|image|max:4096', // hasta 4 MB
    ]);

    // 4) Borrar QR anterior si existe
    if ($prov->codigoqr_prov) {
        Storage::disk('public')->delete($prov->codigoqr_prov);
    }

    // 5) Guardar nuevo archivo en: storage/app/public/proveedores/qr
    $path = $request->file('qr')->store('proveedores/qr', 'public');

    // 6) Actualizar columna en BD
    $prov->codigoqr_prov = $path;
    $prov->save();

    return response()->json([
        'ok'      => true,
        'message' => 'QR actualizado correctamente.',
        'path'    => $path,
    ]);
}
}