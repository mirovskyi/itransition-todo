<?php

declare(strict_types=1);

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface as DataTransformerInterfaceAlias;
use App\Dto\TaskOutput;
use App\Entity\Task;
use App\Utils\WeekScheduler;

class TaskOutputDataTransformer implements DataTransformerInterfaceAlias
{
    /**
     * {@inheritdoc}
     * @param Task $object
     * @return object
     */
    public function transform($object, string $to, array $context = [])
    {
        $output = new TaskOutput();
        $output->uuid = $object->getUuid();
        $output->name = $object->getName();
        $output->description = $object->getDescription();
        $output->enabled = $object->isEnabled();
        $output->priority = $object->getPriority();
        $output->always = $object->isAlways();
        $output->oneDay = $object->getOneDay();
        $output->weekSchedule = $object->getWeekSchedule() ?
            WeekScheduler::convertBitMaskToWeekDaysList($object->getWeekSchedule()) : null;
        $output->createdAt = $object->getCreatedAt();
        $output->updatedAt = $object->getUpdatedAt();

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return TaskOutput::class === $to && $data instanceof Task;
    }
}
