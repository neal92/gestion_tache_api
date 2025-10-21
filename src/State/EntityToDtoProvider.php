<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use App\Dto\UserOutput;
use App\Dto\TaskOutput;
use App\Entity\Users;
use App\Entity\Task;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EntityToDtoProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')] 
        private ProviderInterface $itemProvider
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $item = $this->itemProvider->provide($operation, $uriVariables, $context);
        
        // Si c'est une collection
        if (is_array($item)) {
            $result = [];
            foreach ($item as $entity) {
                $result[] = $this->transformEntityToDto($entity);
            }
            return $result;
        }
        
        // Si c'est un seul élément
        if ($item) {
            return $this->transformEntityToDto($item);
        }
        
        return null;
    }
    
    private function transformEntityToDto(object $entity): object
    {
        if ($entity instanceof Users) {
            $dto = new UserOutput();
            $dto->id = $entity->getId();
            $dto->name = $entity->getName();
            $dto->username = $entity->getUsername();
            $dto->email = $entity->getEmail();
            $dto->role = $entity->getRole();
            $dto->avatar = $entity->getAvatar();
            $dto->created_at = $entity->getCreatedAt();
            return $dto;
        }
        
        if ($entity instanceof Task) {
            $dto = new TaskOutput();
            $dto->id = $entity->getId();
            $dto->title = $entity->getTitle() ?? '';
            $dto->description = $entity->getDescription() ?? '';
            
            // Gestion sécurisée des dates
            $dueDate = $entity->getDueDate();
            $dto->dueDate = $dueDate ? $dueDate->format('Y-m-d H:i:s') : '';
            
            $dto->status = $entity->getStatus() ?? '';
            
            // Gérer les relations
            if ($entity->getAssignedTo()) {
                $dto->assignedTo = [
                    'id' => $entity->getAssignedTo()->getId(),
                    'name' => $entity->getAssignedTo()->getName() ?? ''
                ];
            }
            
            if ($entity->getCreatedBy()) {
                $dto->createdBy = [
                    'id' => $entity->getCreatedBy()->getId(),
                    'name' => $entity->getCreatedBy()->getName() ?? ''
                ];
            }
            
            $createdAt = $entity->getCreatedAt();
            $dto->createdAt = $createdAt ? $createdAt->format('Y-m-d H:i:s') : '';
            
            $updatedAt = $entity->getUpdatedAt();
            if ($updatedAt) {
                $dto->updatedAt = $updatedAt->format('Y-m-d H:i:s');
            }
            
            return $dto;
        }
        
        return $entity;
    }
}