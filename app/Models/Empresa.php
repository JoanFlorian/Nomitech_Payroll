<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresa';
    protected $primaryKey = 'id_empresa';

    protected $fillable = [
        'nit',
        'razon_social',
        'doc_representante',
        'id_ciudad',
        'direccion',
        'correo',
        'telefono'
    ];

    public function representante()
    {
        return $this->belongsTo(Usuario::class, 'doc_representante', 'doc');
    }

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, 'id_ciudad', 'id_ciudad');
    }

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_empresa', 'id_empresa', 'doc');
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'id_empresa', 'id_empresa');
    }

    
    public function licencia()
    {
        return $this->hasOne(Licencia::class, 'empresa_id', 'id_empresa');
    }
}
