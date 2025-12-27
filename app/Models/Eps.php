<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eps extends Model
{
    use HasFactory;

    protected $table = 'eps';
    protected $primaryKey = 'id_eps';
    protected $fillable = ['nombre'];

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'id_eps', 'id_eps');
    }
}
