<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Task;

class TaskDto
{
    #[Assert\NotBlank(
        message: 'Название задачи не может быть пустым'
    )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Название не может быть длиннее {{ limit }} символов'
    )]
    public string $title;
    
    public ?string $description = null;
    
    #[Assert\Choice(
        choices: Task::STATUSES,
        message: 'Выберите допустимый статус: {{ choices }}'
    )]
    public string $status = Task::STATUS_NEW;
}
