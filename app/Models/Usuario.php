<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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
        'avatar',
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
        'horas_diarias',
        'fondo_cesantias',
        'is_owner',
        'must_change_password',
        'email_verified_at',
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
            'is_owner'             => 'boolean',
            'must_change_password' => 'boolean',
            'email_verified_at'    => 'datetime',
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

    public function getNumeroDocumentoAttribute($value): string
    {
        $numeroDocumento = is_string($value) ? trim($value) : $value;

        if ($numeroDocumento !== null && $numeroDocumento !== '') {
            return (string) $numeroDocumento;
        }

        return (string) ($this->attributes['doc'] ?? '');
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'doc', 'doc');
    }

    public function empresa()
    {
        return $this->belongsToMany(Empresa::class, 'usuario_empresa', 'doc', 'id_empresa');
    }

    public function roles()
    {
        // Deprecated: use rol() instead for the single legacy role
        return $this->hasMany(Rol::class, 'id_rol', 'id_rol');
    }

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, 'id_ciudad', 'id_ciudad');
    }

    public function directPermissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id')
                    ->withPivot('company_id', 'active');
    }

    /**
     * Check if the user has a specific permission scoped to the active company.
     * 
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        if ($this->is_owner) {
            return true;
        }

        $id_empresa = session('empresa_id');
        
        // Si no hay empresa activa en sesión, no hay permisos (salvo el owner que ya se validó arriba)
        if (!$id_empresa) {
            return false;
        }

        $cacheKey = "permissions_user_{$this->doc}_company_{$id_empresa}";

        $permissions = Cache::remember($cacheKey, now()->addHours(2), function () use ($id_empresa) {
            // Obtener excepciones directas del usuario para esta empresa
            $overrides = $this->directPermissions()
                ->where('company_id', $id_empresa)
                ->get()
                ->keyBy('name');

            // Obtener permisos del Rol único asignado
            $rolePermissions = $this->rol && $this->rol->permissions 
                ? $this->rol->permissions->pluck('name')->unique()->toArray()
                : [];

            $finalPermissions = [];

            // Procesar permisos del Rol, pero filtrar con las denegaciones (active = 0)
            foreach($rolePermissions as $pName) {
                $override = $overrides->get($pName);
                if ($override && $override->pivot->active == 0) {
                    continue; // Skip denegados
                }
                $finalPermissions[] = $pName;
            }

            // Agregar permisos concedidos explícitamente (active = 1) que no estaban en el rol
            foreach($overrides as $pName => $override) {
                if ($override->pivot->active == 1 && !in_array($pName, $finalPermissions)) {
                    $finalPermissions[] = $pName;
                }
            }

            return $finalPermissions;
        });

        return in_array($permission, $permissions);
    }

    /**
     * Determina la primera ruta accesible para el usuario según sus permisos.
     * Útil para redirecciones inteligentes post-login o cuando se bloquea un acceso.
     */
    public function getFirstAccessibleRoute()
    {
        // Prioridad de rutas y sus permisos requeridos
        $routes = [
            'empleados.index' => 'view_employees',
            'nomina.index' => 'view_payroll',
            'novedades.index' => 'view_novedades',
            'provisiones.index' => 'view_provisions',
            'nomina-electronica.index' => 'view_electronic_payroll',
            'reportes.index' => 'view_reports',
            'periodos.index' => 'view_periods',
            'admin.catalogos.index' => 'manage_catalogos',
            'pila.index' => 'view_pila',
        ];

        foreach ($routes as $route => $permission) {
            if ($this->hasPermission($permission)) {
                return $route;
            }
        }

        // Si no tiene ningún permiso de módulo, pero es trabajador
        if ((int)$this->id_rol == 3) {
            return 'trabajador.dashboard';
        }

        return 'index'; // Nombraremos la ruta raíz como 'index' en web.php
    }

    public function clearPermissionCache()
    {
        $id_empresa = session('empresa_id');
        if ($id_empresa) {
            Cache::forget("permissions_user_{$this->doc}_company_{$id_empresa}");
        }
        
        // Also clear a potential global cache if needed, but scoping it is safer
        Cache::forget("permissions_user_{$this->doc}");
    }

    public function auditLog($action, $module, $entity_id = null)
    {
        $id_empresa = session('empresa_id') ?? ($this->empresa->first()->id_empresa ?? null);

        if (!$id_empresa) return;

        return AuditLog::create([
            'company_id' => $id_empresa,
            'user_id' => $this->doc,
            'action' => $action,
            'module' => $module,
            'entity_id' => $entity_id,
        ]);
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

    public function getAvatarUrlAttribute(): string
    {
        if (!empty($this->avatar)) {
            return Storage::url($this->avatar);
        }

        return asset('images/avatar-default.svg');
    }
}
