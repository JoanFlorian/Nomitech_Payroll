<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'plan';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'valor',
        'num_empl',
        'duracion',
        'stripe_price_id'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    public function licencias()
    {
        return $this->hasMany(Licencia::class, 'plan_id', 'id');
    }
}
