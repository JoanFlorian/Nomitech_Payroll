<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array((int) Auth::user()->id_rol, [1, 4], true)) {
            return redirect('/')->with('error', 'No tienes permisos para administrar catalogos.');
        }

        return $next($request);
    }
}
