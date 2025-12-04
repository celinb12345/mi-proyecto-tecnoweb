<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Producto;
use App\Models\Categoria;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::paraPropietario()->with('categoria');

        if ($request->filled('id_categoria') && $request->id_categoria !== 'todos') {
            $query->where('id_categoria', $request->id_categoria);
        }

        $productos  = $query->orderBy('nombre_prod')->get();
        $categorias = Categoria::orderBy('cat_nombre')
            ->get(['id_categoria', 'cat_nombre', 'cat_descripcion']);

        return response()->json([
            'productos'  => $productos,
            'categorias' => $categorias,
        ]);
    }

  public function store(Request $request)
{
    $validated = $request->validate([
        'nombre_prod'          => 'required|string|max:100',
        'tipo_producto'        => 'required|string|max:80',
        'descripcion_prod'     => 'nullable|string|max:200',
        'precio_unitario_prod' => 'required|numeric|min:0',
        'stock_prod'           => 'nullable|numeric|min:0',
        'prod_disponible'      => 'required|boolean',
        'id_categoria'         => 'required|exists:categoria,id_categoria',
        // OJO: ya no validamos id_unidad aquí
        'foto'                 => 'nullable|image|max:2048',
    ]);

    // Propietario logueado
    $propietario = Auth::user()->propietario ?? null;
    if (!$propietario) {
        return response()->json([
            'success' => false,
            'message' => 'Propietario no encontrado para el usuario actual',
        ], 403);
    }

    $validated['id_propietario'] = $propietario->id_propietario;
    $validated['stock_prod']     = $validated['stock_prod'] ?? 0;

    // ⚠️ Unidad por defecto (asegúrate que exista en unidad_medida)
    $validated['id_unidad'] = 1; // por ejemplo "unidad"

    $producto = new Producto($validated);

    if ($request->hasFile('foto')) {
        $pathFoto = $request->file('foto')->store('productos/fotos', 'public');
        $producto->foto_prod = $pathFoto;
    }

    if ($producto->stock_prod <= 0) {
        $producto->prod_disponible = false;
    }

    $producto->save();

    return response()->json([
        'success'  => true,
        'producto' => $producto,
        'message'  => 'Producto registrado correctamente',
    ]);
}

    public function update(Request $request, $id)
    {
        $producto = Producto::paraPropietario()->findOrFail($id);

        $validated = $request->validate([
            'nombre_prod'          => 'required|string|max:100',
            'tipo_producto'        => 'required|string|max:80',
            'descripcion_prod'     => 'nullable|string|max:200',
            'precio_unitario_prod' => 'required|numeric|min:0',
            'stock_prod'           => 'nullable|numeric|min:0',
            'prod_disponible'      => 'required|boolean',
            'id_categoria'         => 'required|exists:categoria,id_categoria',
            // 🚫 Tampoco validamos id_unidad aquí
            'foto'                 => 'nullable|image|max:2048',
        ]);

        // Tampoco llenamos id_unidad, solo los campos del form
        $producto->fill($validated);
        $producto->stock_prod = $validated['stock_prod'] ?? 0;

        if ($request->hasFile('foto')) {
            if ($producto->foto_prod) {
                Storage::disk('public')->delete($producto->foto_prod);
            }
            $pathFoto = $request->file('foto')->store('productos/fotos', 'public');
            $producto->foto_prod = $pathFoto;
        }

        if ($producto->stock_prod <= 0) {
            $producto->prod_disponible = false;
        }

        $producto->save();

        return response()->json([
            'success'  => true,
            'producto' => $producto,
            'message'  => 'Producto actualizado correctamente',
        ]);
    }

    public function destroy($id)
    {
        $producto = Producto::paraPropietario()->findOrFail($id);

        if ($producto->foto_prod) {
            Storage::disk('public')->delete($producto->foto_prod);
        }

        $producto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado correctamente',
        ]);
    }
}
