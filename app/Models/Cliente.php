<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ScopePropietario;

class Cliente extends Model
{
     use ScopePropietario;
    protected $table = 'cliente';
    protected $primaryKey = 'id_cliente';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_propietario',
        'nombre_cli',
        'apellido_cli',
        'telefono_cli',
        'correo_cli',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'id_propietario', 'id_propietario');
    }

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class, 'id_cliente', 'id_cliente');
    }
}
