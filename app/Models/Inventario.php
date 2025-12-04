<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ScopePropietario;
class Inventario extends Model
{
    use ScopePropietario;

    protected $table = 'inventario';
    protected $primaryKey = 'id_inventario';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_propietario',
        'tipo_movimiento',
        'cantidad_inv',
        'fecha_inv',
        'id_producto',
        'id_unidad',
    ];

    protected $casts = [
        'tipo_movimiento' => 'boolean',
        'fecha_inv' => 'date',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function unidad()
    {
        return $this->belongsTo(UnidadMedida::class, 'id_unidad', 'id_unidad');
    }

    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'id_propietario', 'id_propietario');
    }
}
