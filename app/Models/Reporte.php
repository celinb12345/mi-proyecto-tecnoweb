<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    use HasFactory;

    protected $table = 'reporte';
    protected $primaryKey = 'id_reporte';

    protected $fillable = [
        'tipo_rep',
        'fecha_generacion',
        'id_propietario'
    ];

    protected $casts = [
        'fecha_generacion' => 'date'
    ];

    // RELACIONES
    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'id_propietario');
    }

    // MÉTODOS DE AYUDA
    public function generarReporteProyectos()
    {
        // Lógica para generar reporte de proyectos
        return [
            'total_proyectos' => Proyecto::count(),
            'proyectos_activos' => Proyecto::activos()->count(),
            'ingresos_totales' => Pago::cuotasProyecto()->where('estado_pago', true)->sum('monto_total_pago'),
            'proyectos_completados' => Proyecto::completados()->count()
        ];
    }

    public function generarReporteFinanciero()
    {
        // Lógica para generar reporte financiero
        return [
            'ingresos_clientes' => Pago::cuotasProyecto()->where('estado_pago', true)->sum('monto_total_pago'),
            'gastos_trabajadores' => Pago::sueldos()->where('estado_pago', true)->sum('monto_total_pago'),
            'gastos_materiales' => Compra::sum('total_compra'),
            'balance' => Pago::cuotasProyecto()->where('estado_pago', true)->sum('monto_total_pago') - 
                         (Pago::sueldos()->where('estado_pago', true)->sum('monto_total_pago') + Compra::sum('total_compra'))
        ];
    }
}