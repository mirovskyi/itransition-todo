<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class UserDto
{
    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Email]
    public $email;


    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(value: 8)]
    public $password;

    /**
     * @var string
     */
    #[Assert\LessThanOrEqual(value: 100)]
    public $firstname;

    /**
     * @var string
     */
    #[Assert\LessThanOrEqual(value: 100)]
    public $lastname;

    /**
     * @var array<string>
     */
    public $roles = [];

    public function createUserEntity(): User
    {
        return new User($this->email, $this->roles, $this->firstname, $this->lastname);
    }
}
