<?php 

namespace App\Http\Controllers\Propietario\melamina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Proveedor;

class ProveedorController extends Controller
{
    // LISTAR + BUSCAR (solo del propietario)
    public function index(Request $request)
    {
        $query = Proveedor::paraPropietario();

        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);

            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_empres_prov', 'ILIKE', "%$buscar%")
                  ->orWhere('tipo_materiales_prov', 'ILIKE', "%$buscar%")
                  ->orWhere('telefono_prov', 'ILIKE', "%$buscar%")
                  ->orWhere('correo_prov', 'ILIKE', "%$buscar%");

                if (is_numeric($buscar)) {
                    $q->orWhere('id_proveedor', (int) $buscar);
                }
            });
        }

        $proveedores = $query->orderBy('id_proveedor', 'asc')->get();

        return response()->json([
            'proveedores' => $proveedores,
        ]);
    }

    // REGISTRAR
    public function store(Request $request)
    {
       $validated = $request->validate([
    'id_usuario'            => 'required|string|max:100'
                               . '|exists:usuario,id_usuario'
                               . '|unique:proveedor,id_usuario',
    'nombre_empres_prov'   => 'required|string|max:100',
    'tipo_materiales_prov' => 'required|string|max:100',
    'telefono_prov'        => 'nullable|string|max:20',
    'correo_prov'          => 'nullable|email|max:120',
    'direccion_prov'       => 'nullable|string|max:150',
    'codigoqr'             => 'nullable|image|max:2048',
]);


        $prov = new Proveedor($validated);
        $prov->id_propietario = Auth::user()->propietario->id_propietario;

        if ($request->hasFile('codigoqr')) {
            $pathQR = $request->file('codigoqr')->store('proveedores/qr', 'public');
            $prov->codigoqr_prov = $pathQR;
        }

        $prov->save();

        return response()->json([
            'success'    => true,
            'proveedor'  => $prov,
            'message'    => 'Proveedor registrado correctamente',
        ]);
    }

    // ACTUALIZAR (no cambiamos id_usuario aquí)
    public function update(Request $request, $id)
    {
        $prov = Proveedor::paraPropietario()->findOrFail($id);

        $validated = $request->validate([
            'nombre_empres_prov'   => 'required|string|max:100',
            'tipo_materiales_prov' => 'required|string|max:100',
            'telefono_prov'        => 'nullable|string|max:20',
            'correo_prov'          => 'nullable|email|max:120',
            'direccion_prov'       => 'nullable|string|max:150',
            'codigoqr'             => 'nullable|image|max:2048',
        ]);

        $prov->fill($validated);

        if ($request->hasFile('codigoqr')) {
            if ($prov->codigoqr_prov) {
                Storage::disk('public')->delete($prov->codigoqr_prov);
            }

            $pathQR = $request->file('codigoqr')->store('proveedores/qr', 'public');
            $prov->codigoqr_prov = $pathQR;
        }

        $prov->save();

        return response()->json([
            'success'   => true,
            'proveedor' => $prov,
            'message'   => 'Proveedor actualizado correctamente',
        ]);
    }

    // ELIMINAR
    public function destroy($id)
    {
        $prov = Proveedor::paraPropietario()->findOrFail($id);

        if ($prov->codigoqr_prov) {
            Storage::disk('public')->delete($prov->codigoqr_prov);
        }

        $prov->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proveedor eliminado correctamente',
        ]);
    }
}
