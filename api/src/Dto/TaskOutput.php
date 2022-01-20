<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class TaskOutput
{
    /**
     * Task unique UUID
     * @var string
     */
    #[Groups(['item:get','collection:get'])]
    public $uuid;

    /**
     * Task name
     * @var string
     */
    #[Groups(['item:get','collection:get'])]
    public $name;

    /**
     * Task description
     * @var string
     */
    #[Groups(['item:get','collection:get'])]
    public $description;

    /**
     * Is task enabled
     * @var boolean
     */
    #[Groups(['item:get','collection:get'])]
    public $enabled;

    /**
     * Task prority
     * @var int
     */
    #[Groups(['item:get','collection:get'])]
    public $priority;

    /**
     * Repeat task every day
     * @var bool
     */
    #[Groups(['item:get'])]
    public $always;

    /**
     * Particular date when task is active
     * @var \DateTime
     */
    #[Groups(['item:get'])]
    public $oneDay;

    /**
     * Weekly scheduler. List of week day numbers when task is active, from 0 to 6. 0 - Sunday.
     * @var array<int>
     */
    #[Groups(['item:get'])]
    public $weekSchedule;

    /**
     * Created date time
     * @var \DateTimeImmutable
     */
    #[Groups(['item:get'])]
    public $createdAt;

    /**
     * Last update date time
     * @var \DateTime
     */
    #[Groups(['item:get'])]
    public $updatedAt;
}
