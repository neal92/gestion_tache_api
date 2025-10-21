<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    /**
     * Trouver un utilisateur par email
     * 
     * @param string $email L'email de l'utilisateur
     * @return Users|null L'utilisateur trouvé ou null
     */
    public function findByEmail(string $email): ?Users
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Rechercher des utilisateurs par nom
     * 
     * @param string $name Le nom ou partie du nom à rechercher
     * @return Users[] Tableau d'utilisateurs correspondants
     */
    public function searchByName(string $name): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer les utilisateurs avec leurs tâches assignées
     * 
     * @return Users[] Tableau d'utilisateurs avec leurs tâches
     */
    public function findAllWithTasks(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.tasks', 't')
            ->addSelect('t')
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

        /**
     * Trouver les utilisateurs avec des tâches en retard
     * 
     * @return Users[] Tableau d'utilisateurs avec des tâches en retard
     */
    public function findUsersWithOverdueTasks(): array
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('u')
            ->join('u.tasks', 't')
            ->andWhere('t.dueDate < :now')
            ->andWhere('t.status != :completed')
            ->setParameter('now', $now)
            ->setParameter('completed', 'COMPLETED')
            ->groupBy('u.id')
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Authentifier un utilisateur par email et mot de passe
     * 
     * @param string $email L'email de l'utilisateur
     * @param string $password Le mot de passe non hashé
     * @return Users|null L'utilisateur authentifié ou null si échec
     */
    public function findUserForLogin(string $email): ?Users
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    /**
     * Trouver un utilisateur par son nom d'utilisateur
     * 
     * @param string $username Le nom d'utilisateur
     * @return Users|null L'utilisateur trouvé ou null
     */
    public function findByUsername(string $username): ?Users
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
