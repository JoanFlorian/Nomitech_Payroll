<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Interceptar todas las validaciones de Gate
        Gate::before(function ($user, $ability) {
            // El Owner pasa derecho por todas las verificaciones sin buscar en base de datos
            if ($user->is_owner) {
                return true;
            }

            // Para los demás, revisamos la cache de permisos estructurada en el RBAC
            // Si el RBAC retorna falso, denegamos explícitamente el acceso.
            return (bool) $user->hasPermission($ability);
        });
    }
}
