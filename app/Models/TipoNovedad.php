<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoNovedad extends Model
{
    use HasFactory;

    protected $table = 'tipo_novedad';
    protected $primaryKey = 'id_tipo_novedad';
    protected $fillable = ['nombre'];

    public function novedades()
    {
        return $this->hasMany(Novedad::class, 'id_tipo_novedad', 'id_tipo_novedad');
    }
}
