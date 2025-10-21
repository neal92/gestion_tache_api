<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour la gestion centralisée des erreurs d'API
 */
class ApiErrorController extends AbstractController
{
    /**
     * Gère toutes les routes API non trouvées
     * 
     * @Route("/api/{path}", name="api_not_found", requirements={"path"=".+"})
     */
    public function apiNotFound(Request $request): JsonResponse
    {
        $path = $request->getPathInfo();
        
        return $this->json([
            'code' => Response::HTTP_NOT_FOUND,
            'message' => sprintf('La ressource "%s" n\'existe pas.', $path),
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
        ], Response::HTTP_NOT_FOUND);
    }
    
    /**
     * Page de documentation API
     * 
     * @Route("/api", name="api_docs")
     */
    public function apiDocs(): JsonResponse
    {
        return $this->json([
            'message' => 'API Gestion de Tâches',
            'version' => '1.0.0',
            'endpoints' => [
                '/api/users' => 'Liste des utilisateurs',
                '/api/users/{id}' => 'Détails d\'un utilisateur',
                '/api/tasks' => 'Liste des tâches',
                '/api/tasks/{id}' => 'Détails d\'une tâche'
            ],
            'documentation' => 'Pour plus d\'informations, consultez /api/docs'
        ]);
    }
}