<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Licencia extends Model
{
    use HasFactory;

    protected $table = 'licencia';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'empresa_id',
        'plan_id',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function getEstadoAttribute()
    {
        $hoy = Carbon::now();
        $fechaFin = $this->fecha_fin;

        if ($fechaFin < $hoy) {
            return 'VENCIDA';
        } elseif ($fechaFin->diffInDays($hoy) <= 30) {
            return 'POR_VENCER';
        }
        return 'ACTIVA';
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id_empresa');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'licencia_id', 'id');
    }

    // Legacy isActive() removed to prevent conflicting logic. 
    // Use $licencia->is_active instead.
}
