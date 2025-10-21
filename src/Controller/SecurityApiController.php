<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;

/**
 * Contrôleur pour gérer l'authentification API
 */
class SecurityApiController extends AbstractController
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Endpoint de login pour authentifier l'utilisateur et générer un token
     */
    #[Route("/api/auth/login", name: "api_auth_login", methods: ["POST"])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['email']) || !isset($data['password'])) {
                return $this->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Email et mot de passe requis',
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $email = $data['email'];
            $password = $data['password'];
            
            // Rechercher l'utilisateur par email en utilisant notre méthode de repository
            $user = $this->entityManager->getRepository(Users::class)->findUserForLogin($email);
            
            if (!$user) {
                return $this->json([
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Identifiants invalides',
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            // Vérifier le mot de passe (le mot de passe est stocké hashé)
            if (!password_verify($password, $user->getHashPassword())) {
                return $this->json([
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Identifiants invalides',
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            // Générer un token simple (dans une vraie application, utilisez JWT)
            $token = bin2hex(random_bytes(32));
            $expiresAt = new \DateTime('+1 day');
            
            // En réalité, vous devriez stocker ce token de manière sécurisée
            // Pour cet exemple, nous le renvoyons simplement
            
            return $this->json([
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                    'role' => $user->getRole()
                ],
                'token' => $token,
                'expires_at' => $expiresAt->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Endpoint pour vérifier si un token est valide
     */
    #[Route("/api/auth/verify", name: "api_auth_verify", methods: ["POST"])]
    public function verifyToken(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['token'])) {
                return $this->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Token requis',
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Dans une implémentation réelle, vous vérifieriez la validité du token
            // Pour cet exemple, nous supposons que tous les tokens sont valides
            
            return $this->json([
                'valid' => true,
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Endpoint pour enregistrer un nouvel utilisateur
     */
    #[Route("/api/auth/register", name: "api_auth_register", methods: ["POST"])]
    public function register(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['email']) || !isset($data['password']) || 
                !isset($data['name']) || !isset($data['username'])) {
                return $this->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Données d\'inscription incomplètes',
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Vérifier si l'email existe déjà
            $existingUser = $this->entityManager->getRepository(Users::class)->findOneBy([
                'email' => $data['email']
            ]);
            
            if ($existingUser) {
                return $this->json([
                    'code' => Response::HTTP_CONFLICT,
                    'message' => 'Cet email est déjà utilisé',
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_CONFLICT);
            }
            
            // Créer le nouvel utilisateur
            $user = new Users();
            $user->setName($data['name']);
            $user->setUsername($data['username']);
            $user->setEmail($data['email']);
            $user->setHashPassword(password_hash($data['password'], PASSWORD_DEFAULT));
            $user->setRole('ROLE_USER'); // Rôle par défaut
            $user->setAvatar('default.png'); // Avatar par défaut
            $user->setCreatedAt(date('Y-m-d H:i:s'));
            
            // Persister l'utilisateur en base de données
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            
            // Générer un token pour le nouvel utilisateur
            $token = bin2hex(random_bytes(32));
            $expiresAt = new \DateTime('+1 day');
            
            return $this->json([
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                    'role' => $user->getRole()
                ],
                'token' => $token,
                'expires_at' => $expiresAt->format('Y-m-d H:i:s')
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}