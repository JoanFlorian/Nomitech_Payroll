<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NominaExportacion extends Model
{
    use HasFactory;

    protected $table = 'nomina_exportaciones';

    protected $fillable = [
        'id_periodo',
        'id_empresa',
        'formato',
        'fecha_generacion',
        'doc_usuario',
        'total_empleados',
        'total_pagado',
        'archivo_path',
        'archivo_excel_path',
    ];

    protected $casts = [
        'fecha_generacion' => 'datetime',
        'total_pagado' => 'decimal:2',
    ];

    public function periodo()
    {
        return $this->belongsTo(PeriodoLiquidacion::class, 'id_periodo');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'doc_usuario', 'doc');
    }
}
