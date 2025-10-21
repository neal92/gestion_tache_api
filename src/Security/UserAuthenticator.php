<?php

namespace App\Security;

use App\Entity\Users;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Service pour l'authentification des utilisateurs
 * Cette classe servira de base pour l'intégration avec LexikJWTAuthenticationBundle
 */
class UserAuthenticator
{
    private $userRepository;
    
    public function __construct(UserRepository $userRepository) 
    {
        $this->userRepository = $userRepository;
    }
    
    /**
     * Authentifie un utilisateur par nom d'utilisateur et mot de passe
     * 
     * @param string $username Nom d'utilisateur
     * @param string $password Mot de passe
     * @return Users|null L'utilisateur authentifié ou null
     */
    public function authenticate(string $username, string $password): ?Users
    {
        // Trouver l'utilisateur
        $user = $this->userRepository->findByUsername($username);
        
        // Vérifier si l'utilisateur existe et si le mot de passe est correct
        if ($user && password_verify($password, $user->getHash_password())) {
            return $user;
        }
        
        return null;
    }
}