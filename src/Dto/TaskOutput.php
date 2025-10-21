<?php

namespace App\Dto;

class TaskOutput
{
    public int $id;
    public string $title;
    public string $description;
    public string $dueDate;
    public string $status;
    public ?array $assignedTo = null;
    public ?array $createdBy = null;
    public string $createdAt;
    public ?string $updatedAt = null;
}