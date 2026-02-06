<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
    use HasFactory;

    protected $table = 'ciudad';
    protected $primaryKey = 'id_ciudad';
    protected $fillable = [
        'nombre',
        'codigo',
        'id_departamento'
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'id_ciudad', 'id_ciudad');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_ciudad', 'id_ciudad');
    }
}
