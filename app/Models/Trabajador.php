<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ScopePropietario;

class Trabajador extends Model
{
    use ScopePropietario;
    protected $table = 'trabajador';
    protected $primaryKey = 'id_trabajador';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_propietario',
        'nombre_trabajador',
        'apellido_trabajador',
        'cargo_trabajador',
        'sueldo_trabajador',
        'foto_trabajador',
        'telefono_trabajador',
        'tipo_contrato_trab',
        'saldo_pendiente_trab',
        'codigoqr_trab',
    ];

    protected $casts = [
        'sueldo_trabajador' => 'float',
        'saldo_pendiente_trab' => 'float',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'id_propietario', 'id_propietario');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'id_trabajador', 'id_trabajador');
    }
}
