<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DetalleCompra;
use App\Models\Compra;
use App\Models\Producto;

class DetalleCompraController extends Controller
{
    // LISTAR DETALLES + PRODUCTOS DISPONIBLES
    public function index($id_compra)
    {
        $compra = Compra::with(['proveedor', 'proyecto'])->findOrFail($id_compra);

        $detalles = DetalleCompra::with('producto')
            ->where('id_compra', $id_compra)
            ->orderBy('id_detalle_compra')
            ->get();

        $productos = Producto::orderBy('nombre_prod')
            ->get(['id_producto', 'nombre_prod', 'precio_unitario_prod']);

        return response()->json([
            'compra'    => $compra,
            'detalles'  => $detalles,
            'productos' => $productos,
        ]);
    }

   
public function store(Request $request)   
{
    $data = $request->validate([
        'id_compra'       => 'required|integer|exists:compra,id_compra',
        'id_producto'     => 'required|integer|exists:producto,id_producto',
        'cantidad'        => 'required|numeric|min:1',
        'precio_unitario' => 'required|numeric|min:0',
        'subtotal'        => 'required|numeric|min:0',
    ]);

    // Crear detalle
    $detalle = DetalleCompra::create($data);

    // Recalcular total de la compra
    $total = DetalleCompra::where('id_compra', $data['id_compra'])->sum('subtotal');

    Compra::where('id_compra', $data['id_compra'])
        ->update(['total_compra' => $total]);

    return response()->json([
        'success' => true,
        'detalle' => $detalle,
        'total'   => $total,
    ]);
}

    public function destroy($id_detalle)
    {
        $detalle = DetalleCompra::findOrFail($id_detalle);
        $id_compra = $detalle->id_compra;

        $detalle->delete();

        // Recalcular total de la compra
        $compra = Compra::findOrFail($id_compra);
        $nuevoTotal = DetalleCompra::where('id_compra', $id_compra)->sum('subtotal');
        $compra->total_compra = $nuevoTotal;
        $compra->save();

        return response()->json([
            'success' => true,
            'message' => 'Detalle eliminado correctamente',
        ]);
    }
}
