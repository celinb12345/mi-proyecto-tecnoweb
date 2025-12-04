<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ScopePropietario;

class PlanCuota extends Model
{
    use HasFactory;
    use ScopePropietario;

    protected $table = 'plan_cuota';
    protected $primaryKey = 'id_cuota';
    public $timestamps = false;

    protected $fillable = [
        'id_propietario',
        'id_pago',           // ✅ importante
        'numero_cuota',
        'monto_cuota',
        'fecha_vencimiento',
        'estado_cuota',
        'id_proyecto',
    ];

    protected $casts = [
        'estado_cuota'      => 'boolean',
        'fecha_vencimiento' => 'date',
        'monto_cuota'       => 'float',
        'numero_cuota'      => 'integer',
        'id_pago'           => 'integer',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }

    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'id_propietario', 'id_propietario');
    }

    // 🔁 Antes tenías hasMany, aquí va BELONGS TO
    public function pago()
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }
}
