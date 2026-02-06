<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'doc';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'doc',
        'id_tipo_doc',
        'contrasena',
        'primer_nombre',
        'otros_nombres',
        'primer_apellido',
        'segundo_apellido',
        'id_ciudad',
        'direccion',
        'telefono',
        'correo',
        'id_rol',
        'activo'
    ];

    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

    // Overrides for Custom Auth Fields
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    public function getAuthIdentifierName()
    {
        return 'doc';
    }

    // Relationships
    public function tipoDoc()
    {
        return $this->belongsTo(TipoDoc::class, 'id_tipo_doc', 'id_tipo_doc');
    }

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, 'id_ciudad', 'id_ciudad');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'usuario_modulo', 'doc', 'id_modulo');
    }

    /**
     * Get the companies owned/represented by the user.
     */
    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'doc_representante', 'doc');
    }

    // Legacy support if needed, otherwise this is the pivot relation
    public function empresasAsignadas()
    {
        return $this->belongsToMany(Empresa::class, 'usuario_empresa', 'doc', 'id_empresa');
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'doc', 'doc');
    }
}
