<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaCompensacion extends Model
{
    protected $table = 'cajas_compensacion';

    protected $primaryKey = 'id_caja';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id_caja',
        'nombre',
        'empresa_nit',
        'codigo_pila',
        'telefono',
        'direccion',
        'origen',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Relación con empresas
     */
    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'id_caja');
    }
}
