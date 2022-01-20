<?php

declare(strict_types=1);

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Dto\TaskHistoryInput;
use App\Entity\TaskHistory;

class TaskHistoryInputDataTransformer implements DataTransformerInterface
{
    public function __construct(
        private ValidatorInterface $validator
    ) {
    }

    /**
     * {@inheritDoc}
     * @param TaskHistoryInput $object
     * @return object
     */
    public function transform($object, string $to, array $context = [])
    {
        $this->validator->validate($object, $context);

        return new TaskHistory($object->task, new \DateTimeImmutable());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof TaskHistory) {
            return false;
        }

        return TaskHistory::class === $to && null !== ($context['input']['class'] ?? null);
    }
}
