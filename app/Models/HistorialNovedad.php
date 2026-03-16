<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialNovedad extends Model
{
    protected $table = 'historial_novedades';
    protected $primaryKey = 'id_historial_novedad';
    protected $fillable = [
        'id_novedad',
        'id_salario',
        'empleado_id',
        'tipo_novedad',
        'fecha_inicio',
        'fecha_fin',
        'valor',
        'observaciones',
        'accion',
        'id_usuario',
        'usuario_nombre',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'valor' => 'decimal:2',
    ];

    public function novedad()
    {
        return $this->belongsTo(Novedad::class, 'id_novedad', 'id_novedad');
    }

    public function salario()
    {
        return $this->belongsTo(Salario::class, 'id_salario', 'id_salario');
    }
}
