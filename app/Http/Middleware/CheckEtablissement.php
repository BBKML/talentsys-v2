<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEtablissement
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('etablissement_id')) {
            return redirect()->route('etablissement.select');
        }
        return $next($request);
    }
}
