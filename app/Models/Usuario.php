<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;
    
    // NOTE: This model is not extending Authenticatable to strictly follow the script's schema.
    // If you need authentication, you might want to extend Illuminate\Foundation\Auth\User
    // and implement the necessary methods.

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
        // 'telefono',
        // 'correo',
        // 'id_rol',
        // 'activo'
    ];
    
    protected $hidden = [
        'contrasena',
    ];

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

    public function empresasRepresentadas()
    {
        return $this->hasMany(Empresa::class, 'doc_representante', 'doc');
    }
    
    public function empresas()
    {
        return $this->belongsToMany(Empresa::class, 'usuario_empresa', 'doc', 'id_empresa');
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'doc', 'doc');
    }
}
