<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/Usuario.php
class Usuario extends Model
{
    protected $table = 'nomitech_db_usuario';
    protected $primaryKey = 'doc';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'doc',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'correo',
        'telefono',
        'direccion',
        'id_rol',
        'activo'
    ];

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'doc', 'doc');
    }
}
