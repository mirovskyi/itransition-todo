<?php

declare(strict_types=1);

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\TaskHistoryOutput;
use App\Entity\TaskHistory;

class TaskHistoryOutputDataTransformer implements DataTransformerInterface
{
    /**
     * {@inheritDoc}
     * @param TaskHistory $object
     * @return object
     */
    public function transform($object, string $to, array $context = [])
    {
        $output = new TaskHistoryOutput();
        $output->uuid = $object->getUuid();
        $output->task = $object->getTask()->getUuid();
        $output->completedDate = $object->getCompletedDate();
        $output->createdAt = $object->getCreatedAt();

        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return TaskHistoryOutput::class === $to && $data instanceof TaskHistory;
    }
}
