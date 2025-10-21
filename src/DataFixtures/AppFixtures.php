<?php

namespace App\DataFixtures;

use App\Entity\Users;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer des utilisateurs
        $admin = new Users();
        $admin->setName('Admin User');
        $admin->setUsername('admin');
        $admin->setEmail('admin@example.com');
        $admin->setHashPassword(password_hash('admin123', PASSWORD_DEFAULT));
        $admin->setRole('ROLE_ADMIN');
        $admin->setAvatar('admin.png');
        $admin->setCreatedAt(date('Y-m-d H:i:s'));
        $manager->persist($admin);
        
        $user1 = new Users();
        $user1->setName('John Doe');
        $user1->setUsername('johndoe');
        $user1->setEmail('john@example.com');
        $user1->setHashPassword(password_hash('password123', PASSWORD_DEFAULT));
        $user1->setRole('ROLE_USER');
        $user1->setAvatar('john.png');
        $user1->setCreatedAt(date('Y-m-d H:i:s'));
        $manager->persist($user1);
        
        $user2 = new Users();
        $user2->setName('Jane Smith');
        $user2->setUsername('janesmith');
        $user2->setEmail('jane@example.com');
        $user2->setHashPassword(password_hash('password123', PASSWORD_DEFAULT));
        $user2->setRole('ROLE_USER');
        $user2->setAvatar('jane.png');
        $user2->setCreatedAt(date('Y-m-d H:i:s'));
        $manager->persist($user2);

        // Créer des tâches
        $task1 = new Task();
        $task1->setTitle('Développer le frontend');
        $task1->setDescription('Créer les composants React pour l\'interface utilisateur');
        $task1->setDueDate(new \DateTime('+7 days'));
        $task1->setStatus('à faire');
        $task1->setAssignedTo($user1);
        $task1->setCreatedBy($admin);
        $task1->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($task1);
        
        $task2 = new Task();
        $task2->setTitle('Configurer l\'API');
        $task2->setDescription('Configurer les endpoints API pour le CRUD des tâches');
        $task2->setDueDate(new \DateTime('+3 days'));
        $task2->setStatus('en cours');
        $task2->setAssignedTo($user2);
        $task2->setCreatedBy($admin);
        $task2->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($task2);
        
        $task3 = new Task();
        $task3->setTitle('Réunion de planification');
        $task3->setDescription('Participer à la réunion de planification du sprint');
        $task3->setDueDate(new \DateTime('+1 day'));
        $task3->setStatus('à faire');
        $task3->setAssignedTo($admin);
        $task3->setCreatedBy($admin);
        $task3->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($task3);

        $manager->flush();
    }
}
