<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    use HasFactory;

    protected $table = 'cuenta';
    protected $primaryKey = 'id_cuenta';
    protected $fillable = [
        'id_contrato',
        'id_tipo_cuenta',
        'id_banco',
        'numero_cuenta',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'id_contrato', 'id_contrato');
    }

    public function tipoCuenta()
    {
        return $this->belongsTo(TipoCuenta::class, 'id_tipo_cuenta', 'id_tipo_cuenta');
    }

    public function banco()
    {
        return $this->belongsTo(Banco::class, 'id_banco', 'id_banco');
    }
}
