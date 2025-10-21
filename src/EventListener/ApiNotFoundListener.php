<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class ApiNotFoundListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();
        
        // Ne traiter que les requêtes API qui n'existent pas
        if (!str_starts_with($path, '/api/')) {
            return;
        }
        
        // On peut vérifier si la route existe avec le RouterInterface, 
        // mais ici on laisse le système standard gérer cela
        // Ce listener est principalement pour illustration
    }
}