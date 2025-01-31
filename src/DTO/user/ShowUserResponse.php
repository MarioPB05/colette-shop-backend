<?php

namespace App\DTO\user;

class ShowUserResponse
{
    public int $id;
    public string $name;
    public string $surname;
    public string $username;
    public string $email;
    public int $gems;
    public bool $enabled;

    public function __construct(int $id, string $name, string $username, string $email, int $gems, bool $enabled)
    {
        $this->id = $id;
        $this->name = $name;
        $this->username = $username;
        $this->email = $email;
        $this->gems = $gems;
        $this->enabled = $enabled;
    }
}
