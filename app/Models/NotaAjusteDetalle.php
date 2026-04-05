<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaAjusteDetalle extends Model
{
    protected $table = 'nota_ajuste_detalles';

    protected $fillable = [
        'nota_id',
        'campo',
        'valor_original',
        'valor_corregido',
        'diferencia',
        'es_salarial',
        'guardado_por',
        'aprobado_por',
        'aprobado_en',
    ];

    protected $casts = [
        'valor_original'  => 'float',
        'valor_corregido' => 'float',
        'diferencia'      => 'float',
        'es_salarial'     => 'boolean',
        'aprobado_en'     => 'datetime',
    ];

    public function nota(): BelongsTo
    {
        return $this->belongsTo(NotaAjuste::class, 'nota_id');
    }
}
