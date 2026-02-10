<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'plan';
    protected $fillable = ['nombre', 'descripcion', 'valor', 'num_empl', 'duracion'];
}
