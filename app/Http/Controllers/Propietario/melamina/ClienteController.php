<?php

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cliente;

class ClienteController extends Controller
{
    // 📋 Listar y buscar clientes del propietario logueado
    public function index(Request $request)
    {
        $query = Cliente::paraPropietario(); // scope que ya filtra por propietario

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');

            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_cli', 'ILIKE', "%$buscar%")
                  ->orWhere('apellido_cli', 'ILIKE', "%$buscar%")
                  ->orWhere('id_usuario', 'ILIKE', "%$buscar%");
            });
        }

        $clientes = $query->orderBy('id_cliente')->get();

        if ($request->wantsJson()) {
            return response()->json(['clientes' => $clientes]);
        }

        return inertia('Propietario/Melamina/Clientes', [
            'clientes' => $clientes,
        ]);
    }

    // ➕ Registrar nuevo cliente
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_usuario'   => 'nullable|string|max:100',
                'nombre_cli'   => 'required|string|max:100',
                'apellido_cli' => 'required|string|max:100',
                'telefono_cli' => 'nullable|string|max:20',
                'correo_cli'   => 'nullable|email|max:120',
            ]);

            if (empty($validated['id_usuario'])) {
                $validated['id_usuario'] = null;
            }

            $validated['id_propietario'] = Auth::user()->propietario->id_propietario;

            $cliente = Cliente::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cliente registrado correctamente',
                'cliente' => $cliente,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar cliente: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ✏️ Actualizar cliente (solo del propietario logueado)
    public function update(Request $request, $id)
    {
        $cliente = Cliente::paraPropietario()->findOrFail($id);

        $validated = $request->validate([
            // id_usuario NO lo cambiamos aquí (ya está asociado)
            'nombre_cli'   => 'required|string|max:100',
            'apellido_cli' => 'required|string|max:100',
            'telefono_cli' => 'nullable|string|max:20',
            'correo_cli'   => 'nullable|email|max:120',
        ]);

        $cliente->fill($validated)->save();

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado correctamente',
            'cliente' => $cliente,
        ]);
    }

    // 🗑️ Eliminar cliente (solo del propietario logueado)
    public function destroy($id)
    {
        $cliente = Cliente::paraPropietario()->findOrFail($id);
        $cliente->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado correctamente',
        ]);
    }
}
