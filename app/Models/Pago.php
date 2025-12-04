<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ScopePropietario;

class Pago extends Model
{
    use ScopePropietario;
    protected $table = 'pago';
    protected $primaryKey = 'id_pago';
    public $timestamps = false;

    protected $fillable = [
        'id_propietario',
        'tipo_pago',
        'monto_total_pago',
        'cuotas_pago',
        'estado_pago',
        'fecha_pago',
        'metodo_pago',
        'numero_comprobante',
        'concepto_pago',
        'id_cuota',
        'id_proveedor',
        'id_trabajador',
        'id_cliente',
    ];

    protected $casts = [
        'estado_pago' => 'boolean',
        'fecha_pago'  => 'date',
    ];

    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'id_propietario', 'id_propietario');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'id_trabajador', 'id_trabajador');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function cuota()
    {
        return $this->belongsTo(PlanCuota::class, 'id_cuota', 'id_cuota');
    }
}
