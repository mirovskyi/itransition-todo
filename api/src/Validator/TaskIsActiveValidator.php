<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Task;
use App\Utils\WeekScheduler;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use UnexpectedValueException;

class TaskIsActiveValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof TaskIsActive) {
            throw new UnexpectedTypeException($constraint, TaskIsActive::class);
        }

        if (null === $value) {
            return;
        }
        if (!$value instanceof Task) {
            throw new UnexpectedValueException($value, Task::class);
        }

        if (!$this->isTaskActive($value, new \DateTime())) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ uuid }}', $value->getUuid()->toBinary())
                ->addViolation();
        }
    }

    private function isTaskActive(Task $task, \DateTime $date): bool
    {
        if ($task->isAlways()) {
            return true;
        }
        if ($task->getOneDay() && $task->getOneDay()->format('Ymd') === $date->format('Ymd')) {
            return true;
        }

        return !empty($task->getWeekSchedule()) && WeekScheduler::isActiveOnDate($task->getWeekSchedule(), $date);
    }
}
