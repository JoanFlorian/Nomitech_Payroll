<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arl extends Model
{
    use HasFactory;

    protected $table = 'arl';
    protected $primaryKey = 'id_arl';
    protected $fillable = ['id_arl', 'nombre', 'telefono', 'direccion'];

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'id_arl', 'id_arl');
    }
}
