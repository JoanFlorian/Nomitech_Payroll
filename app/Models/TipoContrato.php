<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoContrato extends Model
{
    use HasFactory;

    protected $table = 'tipo_contrato';
    protected $primaryKey = 'id_tipo_contrato';
    protected $fillable = ['nombre', 'seguridad_social'];
    
    // El contrato de aprendizaje ahora se incluye en la causación de prestaciones por solicitud
    public const TIPO_APRENDIZAJE = 4;
    public const TIPO_PRESTACION_SERVICIOS = 6;
    
    protected $casts = [
        'seguridad_social' => 'boolean',
    ];

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'id_tipo_contrato', 'id_tipo_contrato');
    }
}
