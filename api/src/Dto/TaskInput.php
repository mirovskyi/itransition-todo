<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class TaskInput
{
    /**
     * Task name
     * @var string
     */
    #[Assert\NotBlank(groups: ['write'])]
    #[Assert\Length(min: 1, max: 150, groups: ['write'])]
    #[Groups(groups: ['write'])]
    public $name;

    /**
     * Task description
     * @var string
     */
    #[Assert\Length(max: 255, groups: ['write'])]
    #[Groups(groups: ['write'])]
    public $description;

    /**
     * Is task enabled
     * @var boolean
     */
    #[Assert\Type(type: 'bool', groups: ['write'])]
    #[Groups(groups: ['write'])]
    public $enabled = true;

    /**
     * Task priority
     * @var int
     */
    #[Assert\Type(type: 'int', groups: ['write'])]
    #[Groups(groups: ['write'])]
    public $priority = 0;

    /**
     * Repeat task every day
     * @var bool
     */
    #[Assert\Type(type: 'bool', groups: ['write'])]
    #[Groups(groups: ['write'])]
    public $always;

    /**
     * Particular date when task is active
     * @var \DateTime
     */
    #[Assert\Date(groups: ['write'])]
    #[Groups(groups: ['write'])]
    public $oneDay;

    /**
     * Weekly scheduler. List of week day numbers when task is active, from 0 to 6. 0 - Sunday.
     * @var array<int>
     */
    #[Assert\Type(type: 'array', groups: ['write'])]
    #[Groups(groups: ['write'])]
    public $weekSchedule;
}
