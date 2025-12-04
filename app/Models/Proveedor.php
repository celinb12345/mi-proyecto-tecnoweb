<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ScopePropietario;
class Proveedor extends Model
{
    use ScopePropietario;
    protected $table = 'proveedor';
    protected $primaryKey = 'id_proveedor';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_propietario',
        'nombre_empres_prov',
        'tipo_materiales_prov',
        'telefono_prov',
        'correo_prov',
        'direccion_prov',
        'codigoqr_prov',
        'id_usuario',    
    ];
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
    public function propietario()
    {
        return $this->belongsTo(Propietario::class, 'id_propietario', 'id_propietario');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class, 'id_proveedor', 'id_proveedor');
    }
}
