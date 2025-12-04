<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Propietario extends Model
{
    protected $table = 'propietario';
    protected $primaryKey = 'id_propietario';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'tipo_propietario',
        'especialidad_propietario',
        'nombre_propietario',
        'apellido_propietario',
        'correo_propietario',
        'telefono_propietario',
        'direccion_propietario',
        'foto_propietario',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'id_propietario', 'id_propietario');
    }

    public function trabajadores()
    {
        return $this->hasMany(Trabajador::class, 'id_propietario', 'id_propietario');
    }

    public function proveedores()
    {
        return $this->hasMany(Proveedor::class, 'id_propietario', 'id_propietario');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_propietario', 'id_propietario');
    }

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class, 'id_propietario', 'id_propietario');
    }
}
