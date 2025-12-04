<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    protected $table = 'unidad_medida';
    protected $primaryKey = 'id_unidad';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'nombre_unidad',
        'abreviatura_unidad',
        'descripcion_unidad',
        'estado_unidad',
    ];

    protected $casts = [
        'estado_unidad' => 'boolean',
    ];

    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'id_unidad', 'id_unidad');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_unidad', 'id_unidad');
    }
}
