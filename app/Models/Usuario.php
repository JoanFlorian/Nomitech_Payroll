<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $table = 'usuario';
    protected $primaryKey = 'doc';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'doc',
        'id_tipo_doc',
        'numero_documento',
        'primer_nombre',
        'otros_nombres',
        'primer_apellido',
        'segundo_apellido',
        'correo',
        'telefono',
        'direccion',
        'id_ciudad',
        'id_rol',
        'activo',
        'contrasena',
        // Campos del contrato
        'id_tipo_trabajador',
        'id_sub_tipo_trabajador',
        'id_tipo_contrato',
        'fecha_inicio',
        'fecha_fin',
        'salario_base',
        'salario',
        'id_arl',
        'nivel_riesgo',
        'alto_riesgo',
        'id_forma_pago',
        'id_metodo_pago',
        'tipo_cuenta',
        'numero_cuenta',
        'id_eps',
        'id_afp',
        'codigo_interno',
        'horas_diarias',
        'fondo_cesantias',
        'must_change_password',
    ];

    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'activo'               => 'boolean',
            'alto_riesgo'          => 'boolean',
            'must_change_password' => 'boolean',
            'created_at'           => 'datetime',
            'updated_at'           => 'datetime',
        ];
    }

    // Overrides for Custom Auth Fields
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    public function getAuthIdentifierName()
    {
        return 'doc';
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'doc', 'doc');
    }

    public function empresa()
    {
        return $this->belongsToMany(Empresa::class, 'usuario_empresa', 'doc', 'id_empresa');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function notasAjuste()
    {
        return $this->hasMany(NotaAjuste::class, 'usuario_id', 'doc');
    }

    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->correo;
    }

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    public function routeNotificationForMail($notification)
    {
        return $this->correo;
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim(collect([
            $this->primer_nombre,
            $this->otros_nombres,
            $this->primer_apellido,
            $this->segundo_apellido,
        ])->filter()->implode(' '));
    }
}
