<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCuenta extends Model
{
    use HasFactory;

    protected $table = 'tipo_cuenta';
    protected $primaryKey = 'id_tipo_cuenta';
    protected $fillable = ['nombre'];

    public function cuentas()
    {
        return $this->hasMany(Cuenta::class, 'id_tipo_cuenta', 'id_tipo_cuenta');
    }
}
