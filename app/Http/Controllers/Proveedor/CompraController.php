<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Compra;

class CompraController extends Controller
{
    /**
     * Listar compras PENDIENTES del proveedor logueado (JSON)
     */
      public function pendientes()
    {
        $u = Auth::user();

        // Debe estar logueado y tener relación proveedor
        if (! $u || ! $u->proveedor) {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        $prov = $u->proveedor;

        // Compras pendientes de ESTE proveedor
        $comprasPendientes = Compra::with([
                'proyecto' => function ($q) {
                    // columnas que quieres del proyecto
                    $q->select(
                        'id_proyecto',
                        'nombre_pro',
                        'direccion_pro'   // aquí va tu "ubicación"
                        // si tuvieras latitud/longitud, los añades aquí
                        // 'latitud',
                        // 'longitud',
                    );
                }
            ])
            ->where('id_proveedor', $prov->id_proveedor)
            ->whereRaw("LOWER(estado_compra) = 'pendiente'")
            ->orderByDesc('fecha_compra')
            ->get([
                'id_compra',
                'fecha_compra',
                'observacion_comp',
                'total_compra',
                'metodo_pago_comp',
                'estado_compra',
                'id_proyecto', // clave para relacionar con proyecto
            ]);

        return response()->json([
            'compras' => $comprasPendientes,
        ]);
    }

    /**
     * El proveedor SOLO puede cambiar el estado y el método de pago
     */
    public function actualizar(Request $request, $id)
    {
        $user = Auth::user();
        $prov = $user?->proveedor;

        if (! $prov) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $compra = Compra::where('id_compra', $id)
            ->where('id_proveedor', $prov->id_proveedor)
            ->where('estado_compra', 'Pendiente') // solo pendientes
            ->firstOrFail();

        $data = $request->validate([
            'estado_compra'    => 'required|in:Pendiente,Completada,Anulada',
            'metodo_pago_comp' => 'required|in:EFECTIVO,QR',
            'total_compra'     => 'nullable|numeric|min:0',
        ]);

        $compra->fill($data);
        $compra->save();

        return response()->json([
            'success' => true,
            'compra'  => $compra,
        ]);
    }
}

