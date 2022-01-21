<?php

namespace App\Tests\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Task;
use App\Filter\WeekScheduleFilter;
use App\Utils\WeekScheduler;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class WeekScheduleFilterTest extends TestCase
{
    public function testFilterProperty(): void
    {
        $value = [1,2,3,4,5];

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->method('hasField')->with('weekSchedule')->willReturn(true);
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getClassMetadata')->willReturn($classMetadata);
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn($objectManager);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryNameGeneratorInterface = $this->createMock(QueryNameGeneratorInterface::class);
        $queryNameGeneratorInterface->method('generateParameterName')->with('weekSchedule')->willReturn('weekSchedule');

        $queryBuilder->expects(self::once())->method('getRootAliases')->willReturn(['self']);
        $queryBuilder->expects(self::once())->method('andWhere')->with('BIT_AND(self.weekSchedule, :weekSchedule) > 0');
        $queryBuilder->expects(self::once())->method('setParameter')->with('weekSchedule', WeekScheduler::convertWeekDaysListToBitMask($value));

        $filter = new WeekScheduleFilter($managerRegistry, null, null, ['weekSchedule' => true]);
        $filter->apply($queryBuilder, $queryNameGeneratorInterface, Task::class, null, ['filters' => ['weekSchedule'=> $value]]);
    }
}
