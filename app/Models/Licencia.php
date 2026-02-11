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
        'estado',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function getEstadoAttribute()
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
}
