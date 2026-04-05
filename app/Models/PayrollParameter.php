<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollParameter extends Model
{
    protected $table = 'payroll_parameters';

    protected $fillable = [
        'parametro',
        'valor',
        'descripcion',
        'tipo',
        'vigencia_desde',
        'vigencia_hasta',
        'activo',
    ];

    protected $casts = [
        'valor' => 'decimal:4',
        'vigencia_desde' => 'datetime',
        'vigencia_hasta' => 'datetime',
        'activo' => 'boolean',
    ];

    /**
     * Obtener un parámetro activo por nombre
     */
    public static function obtener(string $parametro, $default = null)
    {
        $param = self::where('parametro', $parametro)
            ->where('activo', true)
            ->first();

        return $param ? $param->valor : $default;
    }

    /**
     * Scope para parámetros vigentes
     */
    public function scopeVigentes($query)
    {
        return $query->where('activo', true)
            ->where(function ($q) {
                $now = now();
                $q->whereNull('vigencia_desde')
                  ->orWhere('vigencia_desde', '<=', $now);
            })
            ->where(function ($q) {
                $now = now();
                $q->whereNull('vigencia_hasta')
                  ->orWhere('vigencia_hasta', '>=', $now);
            });
    }
}
