<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::orderBy('cat_nombre')->get();

        return response()->json([
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cat_nombre'      => 'required|string|max:100',
            'cat_descripcion' => 'nullable|string|max:200',
        ]);

        $cat = Categoria::create($validated);

        return response()->json([
            'success'   => true,
            'categoria' => $cat,
            'message'   => 'Categoría registrada correctamente',
        ]);
    }

    public function update(Request $request, $id)
    {
        $cat = Categoria::findOrFail($id);

        $validated = $request->validate([
            'cat_nombre'      => 'required|string|max:100',
            'cat_descripcion' => 'nullable|string|max:200',
        ]);

        $cat->fill($validated)->save();

        return response()->json([
            'success'   => true,
            'categoria' => $cat,
            'message'   => 'Categoría actualizada correctamente',
        ]);
    }

    public function destroy($id)
    {
        $cat = Categoria::findOrFail($id);
        $cat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada',
        ]);
    }
}
