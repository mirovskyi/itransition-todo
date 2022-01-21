<?php

namespace App\Tests\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Doctrine\CurrentUserExtension;
use App\Entity\Task;
use App\Entity\TaskHistory;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CurrentUserExtensionTest extends TestCase
{
    public function testApplyToTaskCollection(): void
    {
        $this->testApplyToCollection(Task::class, 'self.user');
    }

    public function testApplyToTaskHistoryCollection(): void
    {
        $this->testApplyToCollection(TaskHistory::class, 'task.user', 1);
    }

    public function testApplyToCollectionNotSupported(): void
    {
        $security = $this->createMock(Security::class);
        $security->expects(self::any())->method('getUser')->willReturn(null);
        $queryNameGenerator = $this->createMock(QueryNameGeneratorInterface::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::never())->method('andWhere');

        $extension = new CurrentUserExtension($security);
        $extension->applyToCollection($queryBuilder, $queryNameGenerator, Task::class);

        $security->method('getUser')->willReturn($this->createMock(UserInterface::class));
        $extension->applyToCollection($queryBuilder, $queryNameGenerator, 'SomeOtherEntityClassName');
    }

    private function testApplyToCollection(string $className, string $expectedField, $joinCount = 0): void
    {
        $user = $this->createMock(UserInterface::class);
        $security = $this->createMock(Security::class);
        $security->expects(self::any())->method('getUser')->willReturn($user);

        $queryNameGenerator = $this->createMock(QueryNameGeneratorInterface::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())->method('getRootAliases')->willReturn(['self']);
        if ($joinCount > 0) {
            $queryBuilder->expects(self::exactly($joinCount))->method('join');
        }
        $queryBuilder->expects(self::once())->method('andWhere')->with(sprintf('%s = :user', $expectedField));
        $queryBuilder->expects(self::once())->method('setParameter')->with('user', $user);

        $extension = new CurrentUserExtension($security);
        $extension->applyToCollection($queryBuilder, $queryNameGenerator, $className);
    }
}
