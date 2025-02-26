<?php

namespace App\DTO\user;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 15)]
    public string $username;

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    public string $password;

    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Length(max: 400)]
    public string $surname;

    #[Assert\NotBlank]
    #[Assert\Date]
    public string $birthdate;

    #[Assert\NotBlank]
    #[Assert\Length(min: 9, max: 9)]
    public string $dni;
}
