<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoraRecargoExtra extends Model
{
    use HasFactory;

    protected $table = 'hora_recargo_extra';
    protected $primaryKey = 'id_hora';
    protected $fillable = [
        'id_tipo_hora_recargo',
        'id_salario',
        'cantidad',
        'pago'
    ];

    public function tipoHoraRecargo()
    {
        return $this->belongsTo(TipoHoraRecargo::class, 'id_tipo_hora_recargo', 'id_tipo_hora_recargo');
    }

    public function salario()
    {
        return $this->belongsTo(Salario::class, 'id_salario', 'id_salario');
    }
}
