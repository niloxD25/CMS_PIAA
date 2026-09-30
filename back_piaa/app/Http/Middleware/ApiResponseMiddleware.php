<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;
use App\Helpers\ApiResponse;

class ApiResponseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $response = $next($request);

            if ($response instanceof \Illuminate\Http\JsonResponse &&
                isset($response->original['success'])) {
                return $response;
            }

            return $response;

        } catch (Throwable $e) {
            $status = 500;
            $message = $e->getMessage() ?: 'Erreur interne du serveur';

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = $e->getMessage() ?: $this->getDefaultMessage($status);
            }

            return ApiResponse::error($message, $status);
        }
    }

    /**
     * Définit un message par défaut en fonction du code HTTP
     */
    private function getDefaultMessage(int $status): string
    {
        return match ($status) {
            404 => 'Ressource non trouvée',
            405 => 'Méthode non autorisée',
            401 => 'Non authentifié',
            403 => 'Accès refusé',
            default => 'Erreur interne du serveur'
        };
    }
}
