<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

// Relaciones
use App\Models\Propietario;
use App\Models\Cliente;
use App\Models\Trabajador;
use App\Models\Proveedor;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'contrasenia',
        'estado_usuario',
        'id_propietario',
    ];

    protected $hidden = [
        'contrasenia',
        'remember_token',
    ];

    protected $casts = [
        'estado_usuario' => 'boolean',
    ];

    /**
     * Campo que Laravel usará como contraseña (hash).
     */
    public function getAuthPassword()
    {
        return $this->contrasenia;
    }

    /**
     * Campo que Laravel usará como identificador de login.
     */
    public function getAuthIdentifierName()
    {
        return 'id_usuario';
    }

    /**
     * Mutator: siempre guarda la contraseña hasheada (bcrypt).
     */
    public function setContraseniaAttribute($value)
    {
        // Si ya viene hasheada no la vuelve a hashear
        $this->attributes['contrasenia'] = Hash::needsRehash($value)
            ? Hash::make($value)
            : $value;
    }

    /* ===================== RELACIONES ===================== */

    // Propietario dueño al que pertenece este usuario (si es cliente/trabajador/proveedor)
    public function propietarioDueño()
    {
        return $this->belongsTo(Propietario::class, 'id_propietario', 'id_propietario');
    }

    // Si este usuario ES propietario
    public function propietario()
    {
        return $this->hasOne(Propietario::class, 'id_usuario', 'id_usuario');
    }

    // Si este usuario ES cliente
    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'id_usuario', 'id_usuario');
    }

    // Si este usuario ES trabajador
    public function trabajador()
    {
        return $this->hasOne(Trabajador::class, 'id_usuario', 'id_usuario');
    }

    // Si este usuario ES proveedor
    public function proveedor()
    {
        return $this->hasOne(Proveedor::class, 'id_usuario', 'id_usuario');
    }

    /* ===================== MÉTODOS DE GESTIÓN ===================== */

    public function habilitar()
    {
        $this->update(['estado_usuario' => true]);
    }

    public function deshabilitar()
    {
        $this->update(['estado_usuario' => false]);
    }

    public function estaHabilitado()
    {
        return $this->estado_usuario;
    }

    /* ===================== ROLES ===================== */

    public function getRoleAttribute()
    {
        if ($this->propietario) {
            return 'propietario';
        } elseif ($this->cliente) {
            return 'cliente';
        } elseif ($this->trabajador) {
            return 'trabajador';
        } elseif ($this->proveedor) {
            return 'proveedor';
        }
        return 'usuario';
    }

    public function getNameAttribute()
    {
        if ($this->propietario) {
            return $this->propietario->nombre_propietario
                . ' ' . $this->propietario->apellido_propietario;
        } elseif ($this->cliente) {
            return $this->cliente->nombre_cli
                . ' ' . $this->cliente->apellido_cli;
        } elseif ($this->trabajador) {
            return $this->trabajador->nombre_trabajador
                . ' ' . $this->trabajador->apellido_trabajador;
        } elseif ($this->proveedor) {
            return $this->proveedor->nombre_empres_prov
                ?? $this->id_usuario;
        }
        return $this->id_usuario;
    }

    public function esPropietario()
    {
        return $this->role === 'propietario';
    }

    public function esCliente()
    {
        return $this->role === 'cliente';
    }

    public function esTrabajador()
    {
        return $this->role === 'trabajador';
    }

    public function esProveedor()
    {
        return $this->role === 'proveedor';
    }
}
