<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Récupérer toutes les tâches triées par date d'échéance
     * 
     * @return Task[] Returns an array of Task objects
     */
    public function findAllSortedByDueDate(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer les tâches par statut
     * 
     * @param string $status Le statut à rechercher
     * @return Task[] Returns an array of Task objects
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.status = :status')
            ->setParameter('status', $status)
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer les tâches assignées à un utilisateur spécifique
     * 
     * @param int $userId ID de l'utilisateur
     * @return Task[] Returns an array of Task objects
     */
    public function findByAssignedUser(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.assignedTo', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer les tâches dont la date d'échéance est dans la période spécifiée
     * 
     * @param \DateTimeInterface $start Date de début
     * @param \DateTimeInterface $end Date de fin
     * @return Task[] Returns an array of Task objects
     */
    public function findByDueDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.dueDate >= :start')
            ->andWhere('t.dueDate <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer les tâches urgentes (échéance dans les prochains jours)
     * 
     * @param int $days Nombre de jours à considérer
     * @return Task[] Returns an array of Task objects
     */
    public function findUrgentTasks(int $days = 3): array
    {
        $now = new \DateTime();
        $deadline = (new \DateTime())->modify("+{$days} days");

        return $this->createQueryBuilder('t')
            ->andWhere('t.dueDate >= :now')
            ->andWhere('t.dueDate <= :deadline')
            ->andWhere('t.status != :completed')
            ->setParameter('now', $now)
            ->setParameter('deadline', $deadline)
            ->setParameter('completed', 'COMPLETED')
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Rechercher des tâches par mot clé (dans le titre et la description)
     * 
     * @param string $keyword Mot clé à rechercher
     * @return Task[] Returns an array of Task objects
     */
    public function searchByKeyword(string $keyword): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.title LIKE :keyword OR t.description LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->orderBy('t.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
