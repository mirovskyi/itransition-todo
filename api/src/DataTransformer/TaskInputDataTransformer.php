<?php

declare(strict_types=1);

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInitializerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Dto\TaskInput;
use App\Entity\Task;
use App\Utils\WeekScheduler;
use Symfony\Component\Security\Core\Security;

class TaskInputDataTransformer implements DataTransformerInitializerInterface
{
    public function __construct(
        private Security $security,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * {@inheritdoc}
     * @return object
     */
    public function transform($object, string $to, array $context = [])
    {
        $this->validator->validate($object, $context);

        //By default create task for current date
        if (!$object->always && !$object->oneDay && empty($object->weekSchedule)) {
            $object->oneDay = new \DateTime();
        }

        //Handle updating existing task by PUT and PATCH operations
        /** @var Task $existingTask */
        $existingTask = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? null;
        if ($existingTask) {
            $existingTask->setName($object->name);
            $existingTask->setDescription($object->description);
            $existingTask->setEnabled($object->enabled);
            $existingTask->setPriority($object->priority);
            $existingTask->setAlways($object->always);
            $existingTask->setOneDay($object->oneDay);
            $existingTask->setWeekSchedule(!empty($object->weekSchedule) ? WeekScheduler::convertWeekDaysListToBitMask($object->weekSchedule) : null);
            return $existingTask;
        }

        return new Task(
            $object->name,
            $this->security->getUser(),
            $object->enabled,
            $object->priority,
            $object->description,
            $object->oneDay,
            $object->always,
            $object->weekSchedule ? WeekScheduler::convertWeekDaysListToBitMask($object->weekSchedule) : null
        );
    }

    /**
     * {@inheritdoc}
     * @return object
     */
    public function initialize(string $inputClass, array $context = [])
    {
        /** @var Task $task */
        $task = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? null;
        if (!$task) {
            return null;
        }
        $taskInput = new TaskInput();
        if (isset($context['item_operation_name']) && $context['item_operation_name'] === 'put') {
            //PUT operation should replace record, don't fill object with current values.
            return $taskInput;
        }
        $taskInput->name = $task->getName();
        $taskInput->description = $task->getDescription();
        $taskInput->enabled = $task->isEnabled();
        $taskInput->priority = $task->getPriority();
        $taskInput->always = $task->isAlways();
        $taskInput->oneDay = $task->getOneDay();
        if ($task->getWeekSchedule()) {
            $taskInput->weekSchedule = WeekScheduler::convertBitMaskToWeekDaysList($task->getWeekSchedule());
        }

        return $taskInput;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof Task) {
            return false;
        }

        return Task::class === $to && null !== ($context['input']['class'] ?? null);
    }
}
