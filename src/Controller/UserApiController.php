<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;
use App\Dto\UserOutput;

/**
 * Contrôleur pour gérer l'API des utilisateurs
 */
class UserApiController extends AbstractController
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Liste tous les utilisateurs
     */
    #[Route("/api/users", name: "api_users_list", methods: ["GET"], priority: 100)]
    public function listUsers(): JsonResponse
    {
        try {
            $users = $this->entityManager->getRepository(Users::class)->findAll();
            $usersData = [];
            
            foreach ($users as $user) {
                $userOutput = new UserOutput();
                $userOutput->id = $user->getId() ?? 0;
                $userOutput->name = $user->getName() ?? '';
                $userOutput->email = $user->getEmail() ?? '';
                
                $usersData[] = $userOutput;
            }
            
            return $this->json([
                'users' => $usersData
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
     * Récupérer un utilisateur par ID
     */
    #[Route("/api/users/{id}", name: "api_users_get", methods: ["GET"], priority: 100)]
    public function getUserById(int $id): JsonResponse
    {
        try {
            $user = $this->entityManager->getRepository(Users::class)->find($id);
            
            if (!$user) {
                return $this->json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => sprintf('Utilisateur avec ID %d non trouvé', $id),
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_NOT_FOUND);
            }
            
            $userOutput = new UserOutput();
            $userOutput->id = $user->getId() ?? 0;
            $userOutput->name = $user->getName() ?? '';
            $userOutput->email = $user->getEmail() ?? '';
            
            return $this->json([
                'user' => $userOutput
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
     * Créer un nouvel utilisateur
     */
    #[Route("/api/users", name: "api_users_create", methods: ["POST"], priority: 100)]
    public function createUser(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['name']) || !isset($data['email'])) {
                return $this->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Données invalides ou champs requis manquants',
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Vérifier si l'email existe déjà
            $existingUser = $this->entityManager->getRepository(Users::class)->findByEmail($data['email']);
            
            if ($existingUser) {
                return $this->json([
                    'code' => Response::HTTP_CONFLICT,
                    'message' => 'Un utilisateur avec cet email existe déjà',
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_CONFLICT);
            }
            
            $user = new Users();
            $user->setName($data['name']);
            $user->setEmail($data['email']);
            
            // Persister et flush
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            
            // Créer la réponse avec le nouvel utilisateur
            $userOutput = new UserOutput();
            $userOutput->id = $user->getId();
            $userOutput->name = $user->getName();
            $userOutput->email = $user->getEmail();
            
            return $this->json([
                'user' => $userOutput
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
     * Mettre à jour un utilisateur existant
     */
    #[Route("/api/users/{id}", name: "api_users_update", methods: ["PUT"], priority: 100)]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->entityManager->getRepository(Users::class)->find($id);
            
            if (!$user) {
                return $this->json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => sprintf('Utilisateur avec ID %d non trouvé', $id),
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
            if (isset($data['name'])) {
                $user->setName($data['name']);
            }
            
            if (isset($data['email'])) {
                // Vérifier si l'email existe déjà pour un autre utilisateur
                $existingUser = $this->entityManager->getRepository(Users::class)->findByEmail($data['email']);
                
                if ($existingUser && $existingUser->getId() !== $user->getId()) {
                    return $this->json([
                        'code' => Response::HTTP_CONFLICT,
                        'message' => 'Un utilisateur avec cet email existe déjà',
                        'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                    ], Response::HTTP_CONFLICT);
                }
                
                $user->setEmail($data['email']);
            }
            
            // Flush
            $this->entityManager->flush();
            
            // Créer la réponse avec l'utilisateur mis à jour
            $userOutput = new UserOutput();
            $userOutput->id = $user->getId();
            $userOutput->name = $user->getName();
            $userOutput->email = $user->getEmail();
            
            return $this->json([
                'user' => $userOutput
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
     * Supprimer un utilisateur
     */
    #[Route("/api/users/{id}", name: "api_users_delete", methods: ["DELETE"], priority: 100)]
    public function deleteUser(int $id): JsonResponse
    {
        try {
            $user = $this->entityManager->getRepository(Users::class)->find($id);
            
            if (!$user) {
                return $this->json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => sprintf('Utilisateur avec ID %d non trouvé', $id),
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Supprimer l'utilisateur
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            
            return $this->json([
                'code' => Response::HTTP_OK,
                'message' => sprintf('Utilisateur avec ID %d supprimé avec succès', $id),
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
     * Rechercher des utilisateurs par nom
     */
    #[Route("/api/users/search/{name}", name: "api_users_search", methods: ["GET"], priority: 100)]
    public function searchUsers(string $name): JsonResponse
    {
        try {
            $users = $this->entityManager->getRepository(Users::class)->searchByName($name);
            $usersData = [];
            
            foreach ($users as $user) {
                $userOutput = new UserOutput();
                $userOutput->id = $user->getId() ?? 0;
                $userOutput->name = $user->getName() ?? '';
                $userOutput->email = $user->getEmail() ?? '';
                
                $usersData[] = $userOutput;
            }
            
            return $this->json([
                'name' => $name,
                'users' => $usersData
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
     * Récupérer les utilisateurs avec des tâches en retard
     */
    #[Route("/api/users/overdue-tasks", name: "api_users_overdue_tasks", methods: ["GET"], priority: 100)]
    public function getUsersWithOverdueTasks(): JsonResponse
    {
        try {
            $users = $this->entityManager->getRepository(Users::class)->findUsersWithOverdueTasks();
            $usersData = [];
            
            foreach ($users as $user) {
                $userOutput = new UserOutput();
                $userOutput->id = $user->getId() ?? 0;
                $userOutput->name = $user->getName() ?? '';
                $userOutput->email = $user->getEmail() ?? '';
                
                $usersData[] = $userOutput;
            }
            
            return $this->json([
                'users' => $usersData
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