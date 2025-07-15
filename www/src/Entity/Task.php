<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    
    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]    
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'Название задачи не может быть пустым'
    )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Название не может быть длиннее {{ limit }} символов'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)] 
    #[Assert\Choice(
        choices: Task::STATUSES,
        message: 'Выберите допустимый статус: {{ choices }}'
    )]   
    private ?string $status = self::STATUS_NEW;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
