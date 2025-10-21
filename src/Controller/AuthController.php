<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Contrôleur pour gérer l'authentification
 */
class AuthController extends AbstractController
{
    private $entityManager;
    private $userRepository;
    
    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }
    
    /**
     * Login: vérifier les identifiants et générer un token JWT
     */
    #[Route("/api/login", name: "api_login", methods: ["POST"])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['username']) || !isset($data['password'])) {
                return $this->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Données invalides ou champs requis manquants',
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $username = $data['username'];
            $password = $data['password'];
            
            // Rechercher l'utilisateur
            $user = $this->userRepository->findByUsername($username);
            
            if (!$user) {
                return $this->json([
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Identifiants invalides',
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            // Vérifier le mot de passe
            if (!password_verify($password, $user->getHash_password())) {
                return $this->json([
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Identifiants invalides',
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            // Ici, nous devrions générer un token JWT, mais comme lexik/jwt-authentication-bundle n'est pas installé,
            // nous simulons un token pour le moment.
            // Remplacer ceci par une vraie génération de JWT une fois le bundle installé
            $token = 'simulated_jwt_token_' . bin2hex(random_bytes(16));
            
            return $this->json([
                'user' => [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole()
                ],
                'token' => $token
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
     * Vérifier si un token est valide (pour la démonstration)
     */
    #[Route("/api/check-token", name: "api_check_token", methods: ["POST"])]
    public function checkToken(Request $request): JsonResponse
    {
        // Cette méthode est un placeholder
        // Avec lexik/jwt-authentication-bundle, la validation du token serait automatique
        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => 'Token valide (simulation)',
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);
    }
}