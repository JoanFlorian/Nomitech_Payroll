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
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'correo',
        'telefono',
        'direccion',
        'id_rol',
        'activo',
        'contrasena'
    ];

    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

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
}
