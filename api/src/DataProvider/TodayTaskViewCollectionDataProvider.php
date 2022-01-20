<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Dto\TodayTaskViewDto;
use App\Utils\WeekScheduler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\Security\Core\Security;

class TodayTaskViewCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        return $this->generateQuery()->getResult();
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return TodayTaskViewDto::class === $resourceClass;
    }

    private function generateQuery(): Query
    {
        $query = $this->entityManager->createQuery(
            'SELECT NEW App\Dto\TodayTaskViewDto(t.uuid, t.name, t.description, t.priority, h.uuid) ' .
            'FROM App\Entity\Task t LEFT JOIN App\Entity\TaskHistory h WITH t.id = h.task AND h.completedDate = :date ' .
            'WHERE t.user = :user ' .
            'AND (t.always = :always OR t.oneDay = :date OR BIT_AND(t.weekSchedule, :dateWeekDay) > 0) ' .
            'ORDER BY t.priority DESC'
        );
        $currentDate = new \DateTimeImmutable();
        $query->setParameters([
            'user' => $this->security->getUser(),
            'always' => true,
            'date' => $currentDate,
            'dateWeekDay' => WeekScheduler::getWeekDayBitMask($currentDate)
        ]);

        return $query;
    }
}
