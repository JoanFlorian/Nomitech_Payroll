<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pago';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'empresa_id',
        'licencia_id',
        'referencia',
        'proveedor_pago',
        'valor',
        'moneda',
        'estado_pago',
        'stripe_payment_intent_id',
        'stripe_subscription_id',
        'stripe_session_id',
        'fecha_pago'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'fecha_pago' => 'datetime'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id_empresa');
    }

    public function licencia()
    {
        return $this->belongsTo(Licencia::class, 'licencia_id', 'id');
    }
}
