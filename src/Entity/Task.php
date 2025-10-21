<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity(repositoryClass: TaskRepository::class)]

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['task:read']],
            provider: \App\State\EntityToDtoProvider::class,
            output: \App\Dto\TaskOutput::class
        ),
        new Post(
            normalizationContext: ['groups' => ['task:read']],
            denormalizationContext: ['groups' => ['task:write']],
            provider: \App\State\EntityToDtoProvider::class,
            output: \App\Dto\TaskOutput::class
        ),
        new Get(
            normalizationContext: ['groups' => ['task:read']],
            provider: \App\State\EntityToDtoProvider::class,
            output: \App\Dto\TaskOutput::class
        ),
        new Put(
            normalizationContext: ['groups' => ['task:read']],
            denormalizationContext: ['groups' => ['task:write']],
            provider: \App\State\EntityToDtoProvider::class,
            output: \App\Dto\TaskOutput::class
        ),
        new Delete()
    ]
)]

class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['task:read'])]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Groups(['task:read', 'task:write'])]
    private $title;

    #[ORM\Column(type: "text")]
    #[Groups(['task:read', 'task:write'])]
    private $description;

    #[ORM\Column(type: "datetime")]
    #[Groups(['task:read', 'task:write'])]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\Column(type: "string", length: 20)]
    #[Groups(['task:read', 'task:write'])]
    private $status;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn]
    #[Groups(['task:read', 'task:write'])]
    private ?Users $assignedTo = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn]
    private ?Users $createdBy = null;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAssignedTo(): ?Users
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?Users $assignedTo): self
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    public function getCreatedBy(): ?Users
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Users $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }


}
