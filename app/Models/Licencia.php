<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Licencia extends Model
{
    use HasFactory;

    protected $table = 'licencia';

    protected $fillable = [
        'empresa_id',
        'plan_id',
        'estado',
        'fecha_inicio',
        'fecha_fin'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

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

    public function isActive()
    {
        if ($this->estado !== 'active') {
            return false;
        }

        if ($this->fecha_fin && $this->fecha_fin->isPast()) {
            return false;
        }

        return true;
    }
}
