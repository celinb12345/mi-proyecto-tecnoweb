<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Compra extends Model
{
    protected $table = 'compra';
    protected $primaryKey = 'id_compra';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_propietario',
        'fecha_compra',
        'total_compra',
        'metodo_pago_comp',
        'estado_compra',
        'observacion_comp',
        'id_proveedor',
        'id_proyecto',      // 👈 importante, ya existe en tu tabla
    ];

    protected $casts = [
        'fecha_compra' => 'date',
    ];

    /**
     * Scope para filtrar por el propietario logueado.
     * Se usa en el módulo del propietario (no en el proveedor).
     */
    public function scopeParaPropietario(Builder $query): Builder
    {
        $user = Auth::user();

        if ($user && $user->propietario) {
            $idProp = $user->propietario->id_propietario;
            $query->where('id_propietario', $idProp);
        }

        return $query;
    }

    /* ================= Relaciones ================= */

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'id_compra', 'id_compra');
    }

    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'id_propietario', 'id_propietario');
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }
}
