<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    /**
     * Vérifie que l'utilisateur a validé son numéro de téléphone (session)
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->get('authenticated')) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }

        return $next($request);
    }
}