<?php

namespace App\DTO\user;

class UserChangeRequest
{
    public string $name;
    public string $surname;
    public string $birthDate;
    public string $dni;
    public string $email;

    public function __construct()
    {
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setSurname(string $surname): void
    {
        $this->surname = $surname;
    }

    public function setBirthDate(string $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    public function setDni(string $dni): void
    {
        $this->dni = $dni;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }


}
