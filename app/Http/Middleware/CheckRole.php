<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return redirect('login');
        }

        if ($request->user()->is_owner) {
            return $next($request);
        }

        $hasRole = $request->user()->roles()
            ->where('nombre', $role)
            ->exists();

        if (!$hasRole) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No tienes el rol necesario para esta acción.'], 403);
            }
            
            return redirect('/')->with('error', 'No tienes el rol necesario para esta acción.');
        }

        return $next($request);
    }
}
