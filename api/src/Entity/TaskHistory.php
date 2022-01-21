<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Dto\TaskHistoryInput;
use App\Dto\TaskHistoryOutput;
use App\Repository\TaskHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TaskHistoryRepository::class)]
#[ORM\UniqueConstraint(name: 'task_id_completed_date', columns: ['task_id', 'completed_date'])]
#[ApiResource(
    collectionOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['collection:get']]
        ],
        'post' => [
            'denormalization_context' => ['groups' => ['write']],
            'security_post_denormalize' => 'object.getTask().getUser() == user'
        ]
    ],
    itemOperations: [
        'get' => [
            'security' => 'object.getTask().getUser() == user',
            'normalization_context' => ['groups' => ['item:get']]
        ],
        'delete' => ['security' => 'object.getTask().getUser() == user']
    ],
    attributes: ['security' => 'is_granted("ROLE_USER")'],
    input: TaskHistoryInput::class,
    output: TaskHistoryOutput::class,
)]
#[ApiFilter(SearchFilter::class, properties: ['completedDate' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['completedDate'])]
class TaskHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[ApiProperty(identifier: false)]
    private ?int $id;

    #[ORM\Column(type: 'uuid', unique: true)]
    #[ApiProperty(identifier: true)]
    private Uuid $uuid;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'taskHistory')]
    #[ORM\JoinColumn(name: 'task_id', referencedColumnName: 'id', nullable: false)]
    private Task $task;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $completedDate;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Task $task,
        \DateTimeImmutable $completedDate,
        ?Uuid $uuid = null,
        ?\DateTimeImmutable $createdAt = null
    ) {
        $this->task = $task;
        $this->completedDate = $completedDate;
        $this->uuid = $uuid ?? Uuid::v4();
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getCompletedDate(): \DateTimeImmutable
    {
        return $this->completedDate;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getTask(): Task
    {
        return $this->task;
    }
}
