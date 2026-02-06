<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormaPago extends Model
{
    use HasFactory;

    protected $table = 'forma_pago';
    protected $primaryKey = 'id_forma_pago';
    protected $fillable = ['nombre'];

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'id_forma_pago', 'id_forma_pago');
    }
}
