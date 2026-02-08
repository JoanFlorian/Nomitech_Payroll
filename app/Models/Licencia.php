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
        // 'estado', // REMOVED: State is computed dynamically from dates
        'fecha_inicio',
        'fecha_fin',
    ];

    /**
     * Get the computed active status.
     * The license is active ONLY if the current time is within the valid period.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->fecha_fin && now()->lessThanOrEqualTo($this->fecha_fin);
    }

    /**
     * Scope a query to only include active licenses.
     */
    public function scopeActive($query)
    {
        return $query->where('fecha_fin', '>=', now());
    }

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

    // Legacy isActive() removed to prevent conflicting logic. 
    // Use $licencia->is_active instead.
}
