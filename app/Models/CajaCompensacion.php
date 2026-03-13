<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaCompensacion extends Model
{
    protected $table = 'cajas_compensacion';
    protected $primaryKey = 'id_caja';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'codigo_pila',
        'nombre',
        'telefono',
        'direccion',
    ];

    /**
     * Relación con contratos
     */
    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'id_caja', 'id_caja');
    }
}
