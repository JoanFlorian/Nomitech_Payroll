<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Novedad extends Model
{
    use HasFactory;

    protected $table = 'novedad';
    protected $primaryKey = 'id_novedad';
    protected $fillable = [
        'id_tipo_novedad',
        'id_salario',
        'fecha_inicio',
        'fecha_fin',
        'cantidad',
        'pago'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function tipoNovedad()
    {
        return $this->belongsTo(TipoNovedad::class, 'id_tipo_novedad', 'id_tipo_novedad');
    }

    public function salario()
    {
        return $this->belongsTo(Salario::class, 'id_salario', 'id_salario');
    }
}
