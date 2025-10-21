<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        
        // Vérifier si la requête est pour l'API (commence par /api)
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }
        
        $exception = $event->getThrowable();
        
        // Déterminer le statut HTTP approprié
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        } elseif ($exception instanceof ResourceNotFoundException) {
            $statusCode = Response::HTTP_NOT_FOUND;
        }
        
        // Créer une réponse JSON structurée
        $response = new JsonResponse([
            'code' => $statusCode,
            'message' => $this->getErrorMessage($exception, $statusCode),
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
        ], $statusCode);
        
        // Ajouter des en-têtes si nécessaire
        if ($exception instanceof HttpExceptionInterface) {
            foreach ($exception->getHeaders() as $key => $value) {
                $response->headers->set($key, $value);
            }
        }
        
        $event->setResponse($response);
    }
    
    private function getErrorMessage(\Throwable $exception, int $statusCode): string
    {
        // Personnaliser les messages d'erreur selon le code HTTP
        return match ($statusCode) {
            Response::HTTP_NOT_FOUND => 'La ressource demandée n\'existe pas.',
            Response::HTTP_METHOD_NOT_ALLOWED => 'La méthode HTTP n\'est pas autorisée.',
            Response::HTTP_BAD_REQUEST => 'Requête invalide.',
            Response::HTTP_UNAUTHORIZED => 'Authentification requise.',
            Response::HTTP_FORBIDDEN => 'Accès refusé.',
            Response::HTTP_TOO_MANY_REQUESTS => 'Trop de requêtes. Veuillez réessayer plus tard.',
            Response::HTTP_UNPROCESSABLE_ENTITY => 'Les données fournies sont invalides.',
            default => $exception->getMessage() ?: 'Une erreur s\'est produite.'
        };
    }
}