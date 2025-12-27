<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    use HasFactory;

    protected $table = 'pais';
    protected $primaryKey = 'id_pais';
    protected $fillable = ['nombre'];

    public function departamentos()
    {
        return $this->hasMany(Departamento::class, 'id_pais', 'id_pais');
    }
}
