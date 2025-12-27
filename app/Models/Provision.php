<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provision extends Model
{
    use HasFactory;

    protected $table = 'provision';
    protected $primaryKey = 'id_provision';
    protected $fillable = [
        'id_periodo',
        'id_contrato',
        'cesantias',
        'intereses_cesantias',
        'prima'
    ];

    public function periodo()
    {
        return $this->belongsTo(PeriodoLiquidacion::class, 'id_periodo', 'id_periodo');
    }

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'id_contrato', 'id_contrato');
    }
}
