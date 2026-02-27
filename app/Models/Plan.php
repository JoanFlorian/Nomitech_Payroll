<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plan';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'valor',
        'num_empl',
        'duracion',
        'stripe_price_id',
        'destacado',
        'orden',
        'features'
    ];

    protected $casts = [
        'features' => 'array',
        'destacado' => 'boolean',
        'stripe_price_id' => 'string',
        'valor' => 'decimal:2',
    ];

    /**
     * Mutador para el nombre del plan.
     * Siempre guarda el nombre con la primera letra de cada palabra en mayúscula.
     */
    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = ucwords(mb_strtolower($value));
    }

    public function licencias()
    {
        return $this->hasMany(Licencia::class, 'plan_id', 'id');
    }
}
