<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvisionAutomation extends Model
{
    use HasFactory;

    protected $table = 'provision_automation';

    protected $fillable = [
        'id_empresa',
        'benefit_type',
        'payment_mode',
        'execution_day',
        'execution_month',
        'last_execution_year',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'execution_day' => 'integer',
        'execution_month' => 'integer',
        'last_execution_year' => 'integer',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    /**
     * Get label for benefit type
     */
    public function getBenefitLabelAttribute()
    {
        return match($this->benefit_type) {
            'prima' => 'Prima de Servicios',
            'cesantias' => 'Cesantías',
            'intereses_cesantias' => 'Intereses de Cesantías',
            'vacaciones' => 'Vacaciones',
            default => ucfirst($this->benefit_type)
        };
    }
}
