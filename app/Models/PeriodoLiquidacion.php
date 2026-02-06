<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoLiquidacion extends Model
{
    use HasFactory;

    protected $table = 'periodo_liquidacion';
    protected $primaryKey = 'id_periodo';
    protected $fillable = [
        'fecha_inicio',
        'fecha_fin'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function salarios()
    {
        return $this->hasMany(Salario::class, 'id_periodo', 'id_periodo');
    }

    public function provisiones()
    {
        return $this->hasMany(Provision::class, 'id_periodo', 'id_periodo');
    }
}
