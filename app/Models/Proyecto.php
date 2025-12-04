<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Models\Traits\ScopePropietario;

class Proyecto extends Model
{
    use ScopePropietario;
    protected $table = 'proyecto';
    protected $primaryKey = 'id_proyecto';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_propietario',
        'nombre_pro',
        'descripcion_pro',
        'fecha_inicio_pro',
        'fecha_fin_pro',
        'direccion_pro',
        'monto_total_pro',
        'estado_proyecto',
        'obra_completa',
        'id_cliente',
    ];

    protected $casts = [
        'obra_completa' => 'boolean',
        'fecha_inicio_pro' => 'date',
        'fecha_fin_pro' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'id_propietario', 'id_propietario');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'id_proyecto', 'id_proyecto');
    }

    public function cuotas()
    {
        return $this->hasMany(PlanCuota::class, 'id_proyecto', 'id_proyecto');
    }

    public static function actualizarEstadosPorFecha()
    {
        $hoy = Carbon::today()->toDateString();

        static::whereNotNull('fecha_fin_pro')
            ->whereDate('fecha_fin_pro', '<=', $hoy)
            ->whereIn('estado_proyecto', ['En planificación', 'En ejecución'])
            ->update(['estado_proyecto' => 'Finalizado']);
    }
}
