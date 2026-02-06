<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NominaElectronica extends Model
{
    use HasFactory;

    protected $table = 'nomina_electronica';
    protected $primaryKey = 'id_nomina';
    protected $fillable = [
        'id_salario',
        'cune',
        'fecha_generacion',
        'novedad',
        'nota_ajuste',
        'pdf_ruta'
    ];

    protected $casts = [
        'fecha_generacion' => 'datetime',
        'novedad' => 'boolean',
        'nota_ajuste' => 'boolean',
    ];

    public function salario()
    {
        return $this->belongsTo(Salario::class, 'id_salario', 'id_salario');
    }
}
