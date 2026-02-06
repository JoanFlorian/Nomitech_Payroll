<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    use HasFactory;

    protected $table = 'pais';
    protected $primaryKey = 'id_pais';
    protected $fillable = [
        'nombre',
        'nombre_oficial',
        'codigo_alfa2',
        'codigo_alfa3',
        'codigo_numerico'
    ];

    public function departamentos()
    {
        return $this->hasMany(Departamento::class, 'id_pais', 'id_pais');
    }
}
