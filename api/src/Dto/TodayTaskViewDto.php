<?php

declare(strict_types=1);

namespace App\Dto;

use ApiPlatform\Core\Action\NotFoundAction;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    collectionOperations: [
        'get'
    ],
    itemOperations: [
        'get' => [
            'controller' => NotFoundAction::class,
            'read' => false,
            'output' => false,
        ],
    ],
    shortName: 'TodayTask',
    attributes: ['security' => 'is_granted("ROLE_USER")']
)]
class TodayTaskViewDto
{
    /**
     * Task uuid
     * @var Uuid
     */
    #[ApiProperty(identifier: true)]
    public $uuid;

    /**
     * Task name
     * @var string
     */
    public $name;

    /**
     * Task description
     * @var string
     */
    public $description;

    /**
     * Task priority
     * @var int
     */
    public $priority;

    /**
     * Complete history uuid
     * @var string
     */
    public $historyUuid;

    /**
     * Completed state
     * @var bool
     */
    public $completed;

    public function __construct($uuid, $name, $description, $priority, $historyUuid)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->description = $description;
        $this->priority = $priority;
        $this->historyUuid = $historyUuid;
        $this->completed = $historyUuid !== null;
    }
}
