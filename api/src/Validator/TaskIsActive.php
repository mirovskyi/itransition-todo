<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class TaskIsActive extends Constraint
{
    public string $message = 'Task {{ uuid }} is not active now';
}
