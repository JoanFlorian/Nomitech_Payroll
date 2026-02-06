<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $table = 'departamento';
    protected $primaryKey = 'id_departamento';
    protected $fillable = [
        'nombre',
        'codigo',
        'codigo_iso',
        'id_pais'
    ];

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'id_pais', 'id_pais');
    }

    public function ciudades()
    {
        return $this->hasMany(Ciudad::class, 'id_departamento', 'id_departamento');
    }
}
