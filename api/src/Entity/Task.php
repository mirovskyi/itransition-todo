<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Dto\TaskInput;
use App\Dto\TaskOutput;
use App\Filter\WeekScheduleFilter;
use App\Repository\TaskRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    collectionOperations: [
        'get' => ['normalization_context' => ['groups' => ['collection:get']]],
        'post' => ['denormalization_context' => ['groups' => ['write']]]
    ],
    itemOperations: [
        'get' => [
            'security' => 'object.getUser() == user',
            'normalization_context' => ['groups' => ['item:get']]
        ],
        'put' => [
            'security' => 'object.getUser() == user',
            'denormalization_context' => ['groups' => ['write']]
        ],
        'patch' => [
            'security' => 'object.getUser() == user',
            'denormalization_context' => ['groups' => ['write']]
        ],
        'delete' => [
            'security' => 'object.getUser() == user'
        ]
    ],
    subresourceOperations: [
        'api_tasks_task_histories_get_subresource' => [
            'normalization_context' => ['groups' => ['subresource:collection:get']]
        ]
    ],
    attributes: ['security' => 'is_granted("ROLE_USER")'],
    input: TaskInput::class,
    output: TaskOutput::class
)]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial', 'description' => 'partial', 'oneDay' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(BooleanFilter::class, properties: ['enabled', 'always'])]
#[ApiFilter(WeekScheduleFilter::class, properties: ['weekSchedule'])]
#[ApiFilter(OrderFilter::class, properties: ['name', 'priority', 'createdAt'], arguments: ['orderParameterName' => 'order'])]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[ApiProperty(identifier: false)]
    private ?int $id;

    #[ORM\Column(type: 'uuid', unique: true)]
    #[ApiProperty(identifier: true)]
    #[Groups(groups: ['item:get', 'collection:get'])]
    private Uuid $uuid;

    #[ORM\Column(type: 'string', length: 150)]
    #[Groups(groups: ['item:get', 'collection:get', 'write'])]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(groups: ['item:get', 'collection:get', 'write'])]
    private ?string $description;

    #[ORM\Column(type: 'boolean')]
    #[Groups(groups: ['item:get', 'collection:get', 'write'])]
    private bool $enabled;

    #[ORM\Column(type: 'smallint')]
    #[Groups(groups: ['item:get', 'collection:get', 'write'])]
    private int $priority;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(groups: ['item:get', 'write'])]
    private ?bool $always;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(groups: ['item:get', 'write'])]
    private ?\DateTime $oneDay;

    //TODO: Create custom Doctrine type BIT
    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Groups(groups: ['item:get', 'write'])]
    private ?int $weekSchedule;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(groups: ['item:get', 'collection:get', 'write'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime')]
    #[Groups(groups: ['item:get', 'collection:get', 'write'])]
    private \DateTime $updatedAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private UserInterface $user;

    /**
     * @var Collection<TaskHistory>|null
     */
    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskHistory::class, cascade: ['remove'])]
    #[ApiSubresource]
    private ?Collection $taskHistory;

    public function __construct(
        string $name,
        UserInterface $user,
        bool $enabled = true,
        int $priority = 0,
        ?string $description = null,
        ?\DateTime $oneDay = null,
        ?bool $always = null,
        ?int $weekSchedule = null,
        ?Uuid $uuid = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTime $updatedAt = null
    ) {
        $this->name = $name;
        $this->user = $user;
        $this->enabled = $enabled;
        $this->priority = $priority;
        $this->description = $description;
        $this->oneDay = $oneDay;
        $this->always = $always;
        $this->weekSchedule = $weekSchedule;
        $this->uuid = $uuid ?? Uuid::v4();
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getOneDay(): ?\DateTime
    {
        return $this->oneDay;
    }

    public function setOneDay(?\DateTime $oneDay): void
    {
        $this->oneDay = $oneDay;
    }

    public function isAlways(): ?bool
    {
        return $this->always;
    }

    public function setAlways(?bool $always): void
    {
        $this->always = $always;
    }

    public function getWeekSchedule(): ?int
    {
        return $this->weekSchedule;
    }

    public function setWeekSchedule(?int $weekSchedule): void
    {
        $this->weekSchedule = $weekSchedule;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getTaskHistory(): ?Collection
    {
        return $this->taskHistory;
    }

    #[ORM\PreUpdate]
    public function preUpdate():void
    {
        $this->updatedAt = new \DateTime();
    }
}
