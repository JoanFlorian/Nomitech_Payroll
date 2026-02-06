<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;

    protected $table = 'estado';
    protected $primaryKey = 'id_estado';
    protected $fillable = ['nombre', 'descripcion'];

    public function salarios()
    {
        return $this->hasMany(Salario::class, 'id_estado', 'id_estado');
    }
}
