<?php

declare(strict_types=1);

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Task;
use App\Entity\TaskHistory;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class CurrentUserExtension implements QueryCollectionExtensionInterface
{
    private const SUPPORTED_RESOURCE_USER_FIELDS = [
        Task::class => 'user',
        TaskHistory::class => 'task.user'
    ];

    public function __construct(
        private Security $security
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (null === $this->security->getUser() || !isset(self::SUPPORTED_RESOURCE_USER_FIELDS[$resourceClass])) {
            return;
        }
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $fullUserField = $this->resolveNestedEntitiesAndGetFullFieldName(
            self::SUPPORTED_RESOURCE_USER_FIELDS[$resourceClass],
            $rootAlias,
            $queryBuilder
        );
        $queryBuilder->andWhere(sprintf('%s = :user', $fullUserField));
        $queryBuilder->setParameter('user', $this->security->getUser());
    }

    private function resolveNestedEntitiesAndGetFullFieldName(string $userField, string $rootAlias, QueryBuilder $queryBuilder): string
    {
        if (str_contains($userField, '.')) {
            $nestedEntities = explode('.', $userField);
            $userField = array_pop($nestedEntities);
            foreach ($nestedEntities as $entityAlias) {
                $queryBuilder->join(sprintf('%s.%s', $rootAlias, $entityAlias), $entityAlias);
                $rootAlias = $entityAlias;
            }
        }

        return sprintf('%s.%s', $rootAlias, $userField);
    }
}
