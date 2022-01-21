<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Task;
use App\Validator\TaskIsActive;
use Symfony\Component\Serializer\Annotation\Groups;

class TaskHistoryInput
{
    /**
     * @var Task
     */
    #[TaskIsActive(groups: ['write'])]
    #[Groups(['write'])]
    public $task;
}
