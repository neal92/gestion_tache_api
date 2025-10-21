<?php

namespace App\EventListener;

use ApiPlatform\Validator\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Serializer\SerializerInterface;

class ValidationExceptionListener
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        
        // Vérifier si la requête est pour l'API
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();

        // Ne traiter que les exceptions de validation
        if (!$exception instanceof ValidationException) {
            return;
        }

        $violations = $exception->getConstraintViolationList();
        $errors = [];

        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();
            $errors[$propertyPath][] = $violation->getMessage();
        }

        $data = [
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => 'Validation failed',
            'errors' => $errors,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        $event->setResponse(new JsonResponse($data, Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}