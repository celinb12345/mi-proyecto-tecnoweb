<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimiento_inventario';
    protected $primaryKey = 'id_movimiento';

    protected $fillable = [
        'cantidad_mov',
        'tipo_mov',
        'costo_total_mov',
        'precio_unitario_mov',
        'fecha_mov',
        'id_inventario'
    ];

    protected $casts = [
        'cantidad_mov' => 'decimal:2',
        'costo_total_mov' => 'decimal:2',
        'precio_unitario_mov' => 'decimal:2',
        'fecha_mov' => 'date'
    ];

    // RELACIONES
    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'id_inventario');
    }
}