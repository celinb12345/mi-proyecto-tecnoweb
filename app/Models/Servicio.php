<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ScopePropietario;

class Servicio extends Model
{
    use ScopePropietario;

    protected $table = 'servicio';
    protected $primaryKey = 'id_servicio';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_propietario',
        'nombre_servicio',
        'descripcion_serv',
        'precio_serv',
        'tiempo_estimado_serv',
    ];

    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'id_propietario', 'id_propietario');
    }

    public function proyectos()
    {
        return $this->belongsToMany(
            Proyecto::class,
            'proyecto_servicio',
            'id_servicio',
            'id_proyecto'
        )->withPivot('id_proyecto_servicio', 'precio_unitario', 'sub_total');
    }
}
