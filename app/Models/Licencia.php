<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Licencia extends Model
{
    protected $table = 'licencia';
    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'plan_id',
        'estado',
        'fecha_inicio',
        'fecha_fin'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id_empresa');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
    public function getEstadoCalculadoAttribute()
    {
        if (!$this->fecha_fin) {
            return 'prueba';
        }

        $hoy = Carbon::now();
        $fin = Carbon::parse($this->fecha_fin);

        if ($fin->isPast()) {
            return 'vencida';
        }

        if ($fin->diffInDays($hoy) <= 7) {
            return 'por_vencer';
        }

        return 'activa';
    }
}
