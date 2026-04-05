<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaAjuste extends Model
{
    use HasFactory;

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_RESUELTO = 'resuelto';
    public const ESTADO_OMITIDO = 'omitido';

    protected $table = 'nota_ajustes';

    protected $fillable = [
        'usuario_id',
        'id_salario',
        'id_empresa',
        'mensaje',
        'respuesta_admin',
        'estado',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'doc');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    public function salario()
    {
        return $this->belongsTo(Salario::class, 'id_salario', 'id_salario');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function detalles()
    {
        return $this->hasMany(NotaAjusteDetalle::class, 'nota_id');
    }
}