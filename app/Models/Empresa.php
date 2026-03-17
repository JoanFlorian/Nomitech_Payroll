<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresa';
    protected $primaryKey = 'id_empresa';

    protected $fillable = [
        'nit',
        'nit_dv',
        'razon_social',
        'doc_representante',
        'id_ciudad',
        'direccion',
        'correo',
        'telefono'
    ];

    public function representante()
    {
        return $this->belongsTo(Usuario::class, 'doc_representante', 'doc');
    }

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, 'id_ciudad', 'id_ciudad');
    }

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_empresa', 'id_empresa', 'doc');
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'id_empresa', 'id_empresa');
    }

    /**
     * The active or current license of the company.
     * Returns the LATEST (most recently created) license, as newer purchases replace older ones.
     */
    public function licencia()
    {
        return $this->hasOne(Licencia::class, 'empresa_id', 'id_empresa')
            ->latest('created_at'); // Get the most recently created license
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'empresa_id', 'id_empresa');
    }

    /**
     * Get the count of unique employees with active contracts.
     */
    public function activeEmployeesCount(): int
    {
        return $this->contratos()
            ->whereIn('estado', [
                Contrato::ESTADO_ACTIVO,
                Contrato::ESTADO_POR_VENCER,
                Contrato::ESTADO_PROGRAMADO
            ])
            ->distinct('doc')
            ->count('doc');
    }

    /**
     * Check if the company has reached its plan's employee limit.
     */
    public function hasReachedPlanLimit(): bool
    {
        $licencia = $this->licencia;
        if (!$licencia || !$licencia->plan) {
            return false; // Or default limit if no plan
        }

        $limit = (int) $licencia->plan->num_empl;
        
        // If limit is 0, assume it's unlimited (or define a very high default)
        if ($limit === 0) {
            return false;
        }

        return $this->activeEmployeesCount() >= $limit;
    }
}
