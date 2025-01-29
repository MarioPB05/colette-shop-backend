<?php

namespace App\DTO\user;

class TableUserResponse
{
    public int $id;
    public string $name;
    public string $username;
    public string $email;
    public int $gems;

    public function __construct(int $id, string $name, string $username, string $email, int $gems)
    {
        $this->id = $id;
        $this->name = $name;
        $this->username = $username;
        $this->email = $email;
        $this->gems = $gems;
    }
}
