<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plan';

    protected $fillable = [
        'nombre',
        'descripcion',
        'valor',
        'num_empl',
        'duracion',
        'stripe_price_id'
    ];

    public function licencias()
    {
        return $this->hasMany(Licencia::class, 'plan_id', 'id');
    }
}
