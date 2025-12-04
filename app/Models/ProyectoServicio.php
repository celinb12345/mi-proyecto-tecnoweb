<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProyectoServicio extends Model
{
    protected $table = 'proyecto_servicio';
    protected $primaryKey = 'id_proyecto_servicio';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_proyecto',
        'id_servicio',
        'precio_unitario',
        'sub_total',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }
}
