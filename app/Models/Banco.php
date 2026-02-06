<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banco extends Model
{
    use HasFactory;

    protected $table = 'banco';
    protected $primaryKey = 'id_banco';
    protected $fillable = ['nombre', 'codigo'];

    public function cuentas()
    {
        return $this->hasMany(Cuenta::class, 'id_banco', 'id_banco');
    }
}
