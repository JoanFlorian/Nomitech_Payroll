<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            '/stripe/webhook',
        ]);

        $middleware->alias([
            'ensure_active_license' => \App\Http\Middleware\EnsureActiveLicense::class,
            'is_superadmin'         => \App\Http\Middleware\CheckSuperAdmin::class,
            'admin_empresa'         => \App\Http\Middleware\CheckCompanyAdmin::class,
            'prevent_back_history'  => \App\Http\Middleware\PreventBackHistory::class,
            'contractual_access'    => \App\Http\Middleware\CheckContractualAccess::class,
            'permission'            => \App\Http\Middleware\AuthorizePermission::class,
            'role'                  => \App\Http\Middleware\CheckRole::class,
            'must_change_password'  => \App\Http\Middleware\MustChangePassword::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Permitimos que todos los campos se flasheen a la sesión, incluyendo contraseñas
        $exceptions->dontFlash([]);
    })->create();
