<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NivelRiesgo extends Model
{
    protected $table = 'niveles_riesgo';

    protected $fillable = ['nombre', 'porcentaje'];

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'nivel_riesgo_id');
    }
}