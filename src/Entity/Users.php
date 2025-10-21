<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['user:read']],
            provider: \App\State\EntityToDtoProvider::class,
            output: \App\Dto\UserOutput::class
        ),
        new Post(
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:write']],
            provider: \App\State\EntityToDtoProvider::class,
            output: \App\Dto\UserOutput::class
        ),
        new Get(
            normalizationContext: ['groups' => ['user:read']],
            provider: \App\State\EntityToDtoProvider::class,
            output: \App\Dto\UserOutput::class
        ),
        new Put(
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:write']],
            provider: \App\State\EntityToDtoProvider::class,
            output: \App\Dto\UserOutput::class
        ),
        new Delete()
    ]
)]

class Users
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 50, unique: true)]
    #[Groups(['user:read', 'user:write'])]
    private $name;

    #[ORM\Column(type: "string", length: 50, unique: true)]
    #[Groups(['user:read', 'user:write'])]
    private $username;

    #[ORM\Column(type: "string", length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private $email;

    #[ORM\Column(type: "string", length: 255)]
    #[Groups(['user:write'])]
    private $hash_password;

    #[ORM\Column(type: "string", length: 20)]
    #[Groups(['user:read', 'user:write'])]
    private $role;

    #[ORM\Column(type: "string", length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private $avatar;

    #[ORM\Column(type: "string", length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private $created_at;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    // Annotation pour ne pas sérialiser getHashPassword
    #[Groups(['user:none'])]
    public function getHashPassword(): ?string
    {
        return $this->hash_password;
    }

    public function setHashPassword(string $hash_password): self
    {
        $this->hash_password = $hash_password;

        return $this;
    }
    
    // Ajout de getter/setter cohérents avec le nom de la propriété
    public function getHash_password(): ?string
    {
        return $this->hash_password;
    }

    public function setHash_password(string $hash_password): self
    {
        $this->hash_password = $hash_password;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    // Annotation pour ne pas sérialiser getCreatedAt
    #[Groups(['user:none'])]
    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
    
    // Ajout de getter/setter cohérents avec le nom de la propriété
    public function getCreated_at(): ?string
    {
        return $this->created_at;
    }

    public function setCreated_at(string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}

