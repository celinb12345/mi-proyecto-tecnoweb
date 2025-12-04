<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ScopePropietario;
class Producto extends Model
{ 
    use ScopePropietario;

    protected $table = 'producto';
    protected $primaryKey = 'id_producto';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_propietario',
        'nombre_prod',
        'tipo_producto',
        'descripcion_prod',
        'precio_unitario_prod',
        'stock_prod',
        'prod_disponible',
        'foto_prod',
        'id_categoria',
        'id_unidad',
    ];

    protected $casts = [
        'prod_disponible' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function unidad()
    {
        return $this->belongsTo(UnidadMedida::class, 'id_unidad', 'id_unidad');
    }

    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'id_propietario', 'id_propietario');
    }

    protected static function booted()
    {
        static::saving(function ($producto) {
            if ($producto->stock_prod <= 0) {
                $producto->prod_disponible = false;
            }
        });
    }
}
