<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Task;
use App\Dto\TaskOutput;

/**
 * Contrôleur pour gérer l'API des tâches
 */
class TaskApiController extends AbstractController
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Liste toutes les tâches
     */
    #[Route("/api/tasks", name: "api_tasks_list", methods: ["GET"], priority: 100)]
    public function listTasks(): JsonResponse
    {
        try {
            $tasks = $this->entityManager->getRepository(Task::class)->findAll();
            $tasksData = [];
            
            foreach ($tasks as $task) {
                $taskOutput = new TaskOutput();
                $taskOutput->id = $task->getId() ?? 0;
                $taskOutput->title = $task->getTitle() ?? '';
                $taskOutput->description = $task->getDescription() ?? '';
                
                // Gestion sécurisée des dates
                $dueDate = $task->getDueDate();
                $taskOutput->dueDate = $dueDate ? $dueDate->format('Y-m-d H:i:s') : '';
                
                $taskOutput->status = $task->getStatus() ?? '';
                
                // Gérer les relations
                if ($task->getAssignedTo()) {
                    $taskOutput->assignedTo = [
                        'id' => $task->getAssignedTo()->getId(),
                        'name' => $task->getAssignedTo()->getName() ?? ''
                    ];
                }
                
                if ($task->getCreatedBy()) {
                    $taskOutput->createdBy = [
                        'id' => $task->getCreatedBy()->getId(),
                        'name' => $task->getCreatedBy()->getName() ?? ''
                    ];
                }
                
                $createdAt = $task->getCreatedAt();
                $taskOutput->createdAt = $createdAt ? $createdAt->format('Y-m-d H:i:s') : '';
                
                $updatedAt = $task->getUpdatedAt();
                if ($updatedAt) {
                    $taskOutput->updatedAt = $updatedAt->format('Y-m-d H:i:s');
                }
                
                $tasksData[] = $taskOutput;
            }
            
            return $this->json([
                'tasks' => $tasksData
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 500,
                'message' => $e->getMessage(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], 500);
        }
    }
    
    /**
     * Récupérer une tâche par ID
     */
    #[Route("/api/tasks/{id}", name: "api_tasks_get", methods: ["GET"], priority: 100)]
    public function getTask(int $id): JsonResponse
    {
        try {
            $task = $this->entityManager->getRepository(Task::class)->find($id);
            
            if (!$task) {
                return $this->json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => sprintf('Tâche avec ID %d non trouvée', $id),
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_NOT_FOUND);
            }
            
            $taskOutput = new TaskOutput();
            $taskOutput->id = $task->getId() ?? 0;
            $taskOutput->title = $task->getTitle() ?? '';
            $taskOutput->description = $task->getDescription() ?? '';
            
            // Gestion sécurisée des dates
            $dueDate = $task->getDueDate();
            $taskOutput->dueDate = $dueDate ? $dueDate->format('Y-m-d H:i:s') : '';
            
            $taskOutput->status = $task->getStatus() ?? '';
            
            // Gérer les relations
            if ($task->getAssignedTo()) {
                $taskOutput->assignedTo = [
                    'id' => $task->getAssignedTo()->getId(),
                    'name' => $task->getAssignedTo()->getName() ?? ''
                ];
            }
            
            if ($task->getCreatedBy()) {
                $taskOutput->createdBy = [
                    'id' => $task->getCreatedBy()->getId(),
                    'name' => $task->getCreatedBy()->getName() ?? ''
                ];
            }
            
            $createdAt = $task->getCreatedAt();
            $taskOutput->createdAt = $createdAt ? $createdAt->format('Y-m-d H:i:s') : '';
            
            $updatedAt = $task->getUpdatedAt();
            if ($updatedAt) {
                $taskOutput->updatedAt = $updatedAt->format('Y-m-d H:i:s');
            }
            
            return $this->json([
                'task' => $taskOutput
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 500,
                'message' => $e->getMessage(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], 500);
        }
    }
    
    /**
     * Créer une nouvelle tâche
     */
    #[Route("/api/tasks", name: "api_tasks_create", methods: ["POST"], priority: 100)]
    public function createTask(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['title'])) {
                return $this->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Données invalides ou titre manquant',
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $task = new Task();
            $task->setTitle($data['title']);
            
            if (isset($data['description'])) {
                $task->setDescription($data['description']);
            }
            
            if (isset($data['dueDate'])) {
                try {
                    $task->setDueDate(new \DateTime($data['dueDate']));
                } catch (\Exception $e) {
                    // Ignorer les erreurs de date et ne pas définir la date d'échéance
                }
            }
            
            if (isset($data['status'])) {
                $task->setStatus($data['status']);
            } else {
                $task->setStatus('TODO');
            }
            
            // Définir createdAt
            $task->setCreatedAt(new \DateTimeImmutable());
            
            // Persister et flush
            $this->entityManager->persist($task);
            $this->entityManager->flush();
            
            // Créer la réponse avec la nouvelle tâche
            $taskOutput = new TaskOutput();
            $taskOutput->id = $task->getId();
            $taskOutput->title = $task->getTitle();
            $taskOutput->description = $task->getDescription() ?? '';
            
            $dueDate = $task->getDueDate();
            $taskOutput->dueDate = $dueDate ? $dueDate->format('Y-m-d H:i:s') : '';
            
            $taskOutput->status = $task->getStatus();
            $taskOutput->createdAt = $task->getCreatedAt()->format('Y-m-d H:i:s');
            
            return $this->json([
                'task' => $taskOutput
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 500,
                'message' => $e->getMessage(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], 500);
        }
    }
    
    /**
     * Mettre à jour une tâche existante
     */
    #[Route("/api/tasks/{id}", name: "api_tasks_update", methods: ["PUT"], priority: 100)]
    public function updateTask(int $id, Request $request): JsonResponse
    {
        try {
            $task = $this->entityManager->getRepository(Task::class)->find($id);
            
            if (!$task) {
                return $this->json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => sprintf('Tâche avec ID %d non trouvée', $id),
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_NOT_FOUND);
            }
            
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Données invalides',
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Mise à jour des champs
            if (isset($data['title'])) {
                $task->setTitle($data['title']);
            }
            
            if (isset($data['description'])) {
                $task->setDescription($data['description']);
            }
            
            if (isset($data['dueDate'])) {
                try {
                    $task->setDueDate(new \DateTime($data['dueDate']));
                } catch (\Exception $e) {
                    // Ignorer les erreurs de date
                }
            }
            
            if (isset($data['status'])) {
                $task->setStatus($data['status']);
            }
            
            // Définir updatedAt
            $task->setUpdatedAt(new \DateTime());
            
            // Flush
            $this->entityManager->flush();
            
            // Créer la réponse avec la tâche mise à jour
            $taskOutput = new TaskOutput();
            $taskOutput->id = $task->getId();
            $taskOutput->title = $task->getTitle();
            $taskOutput->description = $task->getDescription() ?? '';
            
            $dueDate = $task->getDueDate();
            $taskOutput->dueDate = $dueDate ? $dueDate->format('Y-m-d H:i:s') : '';
            
            $taskOutput->status = $task->getStatus();
            
            $createdAt = $task->getCreatedAt();
            $taskOutput->createdAt = $createdAt ? $createdAt->format('Y-m-d H:i:s') : '';
            
            $updatedAt = $task->getUpdatedAt();
            if ($updatedAt) {
                $taskOutput->updatedAt = $updatedAt->format('Y-m-d H:i:s');
            }
            
            return $this->json([
                'task' => $taskOutput
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 500,
                'message' => $e->getMessage(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], 500);
        }
    }
    
    /**
     * Supprimer une tâche
     */
    #[Route("/api/tasks/{id}", name: "api_tasks_delete", methods: ["DELETE"], priority: 100)]
    public function deleteTask(int $id): JsonResponse
    {
        try {
            $task = $this->entityManager->getRepository(Task::class)->find($id);
            
            if (!$task) {
                return $this->json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => sprintf('Tâche avec ID %d non trouvée', $id),
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Supprimer la tâche
            $this->entityManager->remove($task);
            $this->entityManager->flush();
            
            return $this->json([
                'code' => Response::HTTP_OK,
                'message' => sprintf('Tâche avec ID %d supprimée avec succès', $id),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 500,
                'message' => $e->getMessage(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], 500);
        }
    }
    
    /**
     * Récupérer les tâches par statut
     */
    #[Route("/api/tasks/status/{status}", name: "api_tasks_by_status", methods: ["GET"], priority: 100)]
    public function getTasksByStatus(string $status): JsonResponse
    {
        try {
            $tasks = $this->entityManager->getRepository(Task::class)->findByStatus($status);
            $tasksData = [];
            
            foreach ($tasks as $task) {
                $taskOutput = new TaskOutput();
                $taskOutput->id = $task->getId() ?? 0;
                $taskOutput->title = $task->getTitle() ?? '';
                $taskOutput->description = $task->getDescription() ?? '';
                
                $dueDate = $task->getDueDate();
                $taskOutput->dueDate = $dueDate ? $dueDate->format('Y-m-d H:i:s') : '';
                
                $taskOutput->status = $task->getStatus() ?? '';
                
                if ($task->getAssignedTo()) {
                    $taskOutput->assignedTo = [
                        'id' => $task->getAssignedTo()->getId(),
                        'name' => $task->getAssignedTo()->getName() ?? ''
                    ];
                }
                
                if ($task->getCreatedBy()) {
                    $taskOutput->createdBy = [
                        'id' => $task->getCreatedBy()->getId(),
                        'name' => $task->getCreatedBy()->getName() ?? ''
                    ];
                }
                
                $createdAt = $task->getCreatedAt();
                $taskOutput->createdAt = $createdAt ? $createdAt->format('Y-m-d H:i:s') : '';
                
                $updatedAt = $task->getUpdatedAt();
                if ($updatedAt) {
                    $taskOutput->updatedAt = $updatedAt->format('Y-m-d H:i:s');
                }
                
                $tasksData[] = $taskOutput;
            }
            
            return $this->json([
                'status' => $status,
                'tasks' => $tasksData
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 500,
                'message' => $e->getMessage(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], 500);
        }
    }
    
    /**
     * Récupérer les tâches triées par date d'échéance
     */
    #[Route("/api/tasks/sort/due-date", name: "api_tasks_sort_due_date", methods: ["GET"], priority: 100)]
    public function getTasksSortedByDueDate(): JsonResponse
    {
        try {
            $tasks = $this->entityManager->getRepository(Task::class)->findAllSortedByDueDate();
            $tasksData = [];
            
            foreach ($tasks as $task) {
                $taskOutput = new TaskOutput();
                $taskOutput->id = $task->getId() ?? 0;
                $taskOutput->title = $task->getTitle() ?? '';
                $taskOutput->description = $task->getDescription() ?? '';
                
                $dueDate = $task->getDueDate();
                $taskOutput->dueDate = $dueDate ? $dueDate->format('Y-m-d H:i:s') : '';
                
                $taskOutput->status = $task->getStatus() ?? '';
                
                if ($task->getAssignedTo()) {
                    $taskOutput->assignedTo = [
                        'id' => $task->getAssignedTo()->getId(),
                        'name' => $task->getAssignedTo()->getName() ?? ''
                    ];
                }
                
                if ($task->getCreatedBy()) {
                    $taskOutput->createdBy = [
                        'id' => $task->getCreatedBy()->getId(),
                        'name' => $task->getCreatedBy()->getName() ?? ''
                    ];
                }
                
                $createdAt = $task->getCreatedAt();
                $taskOutput->createdAt = $createdAt ? $createdAt->format('Y-m-d H:i:s') : '';
                
                $updatedAt = $task->getUpdatedAt();
                if ($updatedAt) {
                    $taskOutput->updatedAt = $updatedAt->format('Y-m-d H:i:s');
                }
                
                $tasksData[] = $taskOutput;
            }
            
            return $this->json([
                'tasks' => $tasksData
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 500,
                'message' => $e->getMessage(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], 500);
        }
    }
    
    /**
     * Récupérer les tâches assignées à un utilisateur spécifique
     */
    #[Route("/api/tasks/user/{userId}", name: "api_tasks_by_user", methods: ["GET"], priority: 100)]
    public function getTasksByUser(int $userId): JsonResponse
    {
        try {
            $tasks = $this->entityManager->getRepository(Task::class)->findByAssignedUser($userId);
            $tasksData = [];
            
            foreach ($tasks as $task) {
                $taskOutput = new TaskOutput();
                $taskOutput->id = $task->getId() ?? 0;
                $taskOutput->title = $task->getTitle() ?? '';
                $taskOutput->description = $task->getDescription() ?? '';
                
                $dueDate = $task->getDueDate();
                $taskOutput->dueDate = $dueDate ? $dueDate->format('Y-m-d H:i:s') : '';
                
                $taskOutput->status = $task->getStatus() ?? '';
                
                if ($task->getAssignedTo()) {
                    $taskOutput->assignedTo = [
                        'id' => $task->getAssignedTo()->getId(),
                        'name' => $task->getAssignedTo()->getName() ?? ''
                    ];
                }
                
                if ($task->getCreatedBy()) {
                    $taskOutput->createdBy = [
                        'id' => $task->getCreatedBy()->getId(),
                        'name' => $task->getCreatedBy()->getName() ?? ''
                    ];
                }
                
                $createdAt = $task->getCreatedAt();
                $taskOutput->createdAt = $createdAt ? $createdAt->format('Y-m-d H:i:s') : '';
                
                $updatedAt = $task->getUpdatedAt();
                if ($updatedAt) {
                    $taskOutput->updatedAt = $updatedAt->format('Y-m-d H:i:s');
                }
                
                $tasksData[] = $taskOutput;
            }
            
            return $this->json([
                'userId' => $userId,
                'tasks' => $tasksData
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 500,
                'message' => $e->getMessage(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], 500);
        }
    }
    
    /**
     * Récupérer les tâches urgentes (échéance dans les prochains jours)
     */
    #[Route("/api/tasks/urgent/{days}", name: "api_tasks_urgent", methods: ["GET"], priority: 100)]
    public function getUrgentTasks(int $days = 3): JsonResponse
    {
        try {
            $tasks = $this->entityManager->getRepository(Task::class)->findUrgentTasks($days);
            $tasksData = [];
            
            foreach ($tasks as $task) {
                $taskOutput = new TaskOutput();
                $taskOutput->id = $task->getId() ?? 0;
                $taskOutput->title = $task->getTitle() ?? '';
                $taskOutput->description = $task->getDescription() ?? '';
                
                $dueDate = $task->getDueDate();
                $taskOutput->dueDate = $dueDate ? $dueDate->format('Y-m-d H:i:s') : '';
                
                $taskOutput->status = $task->getStatus() ?? '';
                
                if ($task->getAssignedTo()) {
                    $taskOutput->assignedTo = [
                        'id' => $task->getAssignedTo()->getId(),
                        'name' => $task->getAssignedTo()->getName() ?? ''
                    ];
                }
                
                if ($task->getCreatedBy()) {
                    $taskOutput->createdBy = [
                        'id' => $task->getCreatedBy()->getId(),
                        'name' => $task->getCreatedBy()->getName() ?? ''
                    ];
                }
                
                $createdAt = $task->getCreatedAt();
                $taskOutput->createdAt = $createdAt ? $createdAt->format('Y-m-d H:i:s') : '';
                
                $updatedAt = $task->getUpdatedAt();
                if ($updatedAt) {
                    $taskOutput->updatedAt = $updatedAt->format('Y-m-d H:i:s');
                }
                
                $tasksData[] = $taskOutput;
            }
            
            return $this->json([
                'days' => $days,
                'tasks' => $tasksData
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 500,
                'message' => $e->getMessage(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], 500);
        }
    }
    
    /**
     * Rechercher des tâches par mot clé
     */
    #[Route("/api/tasks/search/{keyword}", name: "api_tasks_search", methods: ["GET"], priority: 100)]
    public function searchTasks(string $keyword): JsonResponse
    {
        try {
            $tasks = $this->entityManager->getRepository(Task::class)->searchByKeyword($keyword);
            $tasksData = [];
            
            foreach ($tasks as $task) {
                $taskOutput = new TaskOutput();
                $taskOutput->id = $task->getId() ?? 0;
                $taskOutput->title = $task->getTitle() ?? '';
                $taskOutput->description = $task->getDescription() ?? '';
                
                $dueDate = $task->getDueDate();
                $taskOutput->dueDate = $dueDate ? $dueDate->format('Y-m-d H:i:s') : '';
                
                $taskOutput->status = $task->getStatus() ?? '';
                
                if ($task->getAssignedTo()) {
                    $taskOutput->assignedTo = [
                        'id' => $task->getAssignedTo()->getId(),
                        'name' => $task->getAssignedTo()->getName() ?? ''
                    ];
                }
                
                if ($task->getCreatedBy()) {
                    $taskOutput->createdBy = [
                        'id' => $task->getCreatedBy()->getId(),
                        'name' => $task->getCreatedBy()->getName() ?? ''
                    ];
                }
                
                $createdAt = $task->getCreatedAt();
                $taskOutput->createdAt = $createdAt ? $createdAt->format('Y-m-d H:i:s') : '';
                
                $updatedAt = $task->getUpdatedAt();
                if ($updatedAt) {
                    $taskOutput->updatedAt = $updatedAt->format('Y-m-d H:i:s');
                }
                
                $tasksData[] = $taskOutput;
            }
            
            return $this->json([
                'keyword' => $keyword,
                'tasks' => $tasksData
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => 500,
                'message' => $e->getMessage(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], 500);
        }
    }
}