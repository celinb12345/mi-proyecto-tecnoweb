<?php

namespace App\Models\Traits;

use App\Models\Propietario;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


trait ScopePropietario
{
    /**
     * Uso:
     *  Modelo::paraPropietario()                       // usa auth()->user()
     *  Modelo::paraPropietario($usuario)               // Usuario
     *  Modelo::paraPropietario($propietario)           // Propietario
     *  Modelo::paraPropietario($idPropietario)         // id_propietario
     */
    public function scopeParaPropietario(Builder $query, $propietarioOrUser = null)
    {
        // Si no mandas nada, tomamos el usuario logueado
        if (is_null($propietarioOrUser)) {
          $propietarioOrUser = Auth::user();

        }

        $propietarioId = null;

        // Si es Usuario (tu modelo Usuario)
        if ($propietarioOrUser instanceof Usuario) {
            $propietarioId = optional($propietarioOrUser->propietario)->id_propietario;

        // Si es Propietario
        } elseif ($propietarioOrUser instanceof Propietario) {
            $propietarioId = $propietarioOrUser->id_propietario;

        // Si es número (id_propietario)
        } else {
            $propietarioId = $propietarioOrUser;
        }

        if (!$propietarioId) {
            // Si no hay propietario, devolvemos el query vacío (para no mostrar nada por error)
            return $query->whereRaw('1 = 0');
        }

        // Nombre de la tabla del modelo
        $table = $query->getModel()->getTable();

        return $query->where("$table.id_propietario", $propietarioId);
    }
}
