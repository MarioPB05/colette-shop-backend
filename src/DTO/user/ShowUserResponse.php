<?php

namespace App\DTO\user;

class ShowUserResponse
{
    public int $id;
    public string $name;
    public string $surname;
    public string $brawlTag;
    public string $username;
    public string $email;
    public string $dni;
    public string $birthdate;
    public bool $enabled;
    public string $avatar;

    public function __construct(
        int $id,
        string $name,
        string $surname,
        string $brawlTag,
        string $username,
        string $email,
        string $dni,
        string $birthdate,
        bool $enabled,
        string $avatar
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->surname = $surname;
        $this->brawlTag = $brawlTag;
        $this->username = $username;
        $this->email = $email;
        $this->dni = $dni;
        $this->birthdate = $birthdate;
        $this->enabled = $enabled;
        $this->avatar = $avatar;
    }
}
