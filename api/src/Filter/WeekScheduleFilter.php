<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Utils\WeekScheduler;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class WeekScheduleFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (
            !$this->isPropertyEnabled($property, $resourceClass) ||
            !$this->isPropertyMapped($property, $resourceClass) ||
            $value == ''
        ) {
            return;
        }
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $propertyName = $queryNameGenerator->generateParameterName($property);
        $queryBuilder->andWhere(sprintf('BIT_AND(%s.%s, :%s) > 0', $rootAlias, $property, $propertyName));
        $queryBuilder->setParameter($propertyName, WeekScheduler::convertWeekDaysListToBitMask($value));
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $propertyName = $this->normalizePropertyName($property);
            $description[$propertyName] = [
                'property' => $propertyName,
                'type' => Type::BUILTIN_TYPE_ARRAY,
                'required' => false,
                'swagger' => [
                    'description' => 'Weekly scheduler - array of week day indexes, starts from 0 (sunday)',
                ]
            ];
        }

        return $description;
    }
}
