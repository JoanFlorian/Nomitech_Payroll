<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    use HasFactory;

    protected $table = 'contrato';
    protected $primaryKey = 'id_contrato';
    protected $fillable = [
        'id_empresa',
        'doc',
        'id_tipo_contrato',
        'id_tipo_trabajador',
        'id_sub_tipo_trabajador',
        'id_forma_pago',
        'id_metodo_pago',
        'id_arl',
        'id_eps',
        'id_afp',
        'alto_riesgo',
        'nivel_riesgo',
        'fecha_inicio',
        'fecha_fin',
        'salario_base',
        'activo'
    ];

    protected $casts = [
        'alto_riesgo' => 'boolean',
        'activo' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'doc', 'doc');
    }

    public function tipoContrato()
    {
        return $this->belongsTo(TipoContrato::class, 'id_tipo_contrato', 'id_tipo_contrato');
    }

    public function tipoTrabajador()
    {
        return $this->belongsTo(TipoTrabajador::class, 'id_tipo_trabajador', 'id_tipo_trabajador');
    }

    public function subTipoTrabajador()
    {
        return $this->belongsTo(SubTipoTrabajador::class, 'id_sub_tipo_trabajador', 'id_sub_tipo_trabajador');
    }

    public function formaPago()
    {
        return $this->belongsTo(FormaPago::class, 'id_forma_pago', 'id_forma_pago');
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'id_metodo_pago', 'id_metodo_pago');
    }

    public function arl()
    {
        return $this->belongsTo(Arl::class, 'id_arl', 'id_arl');
    }

    public function eps()
    {
        return $this->belongsTo(Eps::class, 'id_eps', 'id_eps');
    }

    public function afp()
    {
        return $this->belongsTo(Afp::class, 'id_afp', 'id_afp');
    }

    public function cuentas()
    {
        return $this->hasMany(Cuenta::class, 'id_contrato', 'id_contrato');
    }

    public function salarios()
    {
        return $this->hasMany(Salario::class, 'id_contrato', 'id_contrato');
    }

    public function provisiones()
    {
        return $this->hasMany(Provision::class, 'id_contrato', 'id_contrato');
    }

}
