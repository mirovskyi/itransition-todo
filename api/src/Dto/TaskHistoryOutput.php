<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

class TaskHistoryOutput
{
    /**
     * Task history UUID
     * @var Uuid
     */
    #[Groups(['item:get', 'collection:get', 'subresource:collection:get'])]
    public $uuid;

    /**
     * Task UUID
     * @var string
     */
    #[Groups(['item:get', 'collection:get'])]
    public $task;

    /**
     * Completed date time
     * @var \DateTimeImmutable
     */
    #[Groups(['item:get', 'collection:get', 'subresource:collection:get'])]
    public $completedDate;

    /**
     * Created date time
     * @var \DateTimeImmutable
     */
    #[Groups(['item:get'])]
    public $createdAt;
}
