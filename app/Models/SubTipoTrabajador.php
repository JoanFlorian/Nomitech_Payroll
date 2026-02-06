<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTipoTrabajador extends Model
{
    use HasFactory;

    protected $table = 'sub_tipo_trabajador';
    protected $primaryKey = 'id_sub_tipo_trabajador';
    protected $fillable = ['nombre'];

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'id_sub_tipo_trabajador', 'id_sub_tipo_trabajador');
    }
}
