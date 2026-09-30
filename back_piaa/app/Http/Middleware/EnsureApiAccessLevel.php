<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Projets;

class EnsureApiAccessLevel
{
    public function handle(Request $request, Closure $next)
    {
        $niveauAcces = Projets::NIVEAU_ACCES_DEFAUT;

        $token = $request->bearerToken();

        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);

            if ($accessToken && $accessToken->tokenable) {
                $niveauAcces = $request->get('niveau_acces', $niveauAcces);
            }
        }

        // Injecte le niveau d’accès dans la requête
        $request->merge([
            'niveau_acces' => $niveauAcces
        ]);

        return $next($request);
    }
}
