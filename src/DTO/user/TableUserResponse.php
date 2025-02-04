<?php

namespace App\DTO\user;

class TableUserResponse
{
    public int $id;
    public string $name;
    public string $brawlTag;
    public string $email;
    public int $gems;
    public bool $enabled;

    public function __construct(int $id, string $name, string $brawlTag, string $email, int $gems, bool $enabled)
    {
        $this->id = $id;
        $this->name = $name;
        $this->brawlTag = $brawlTag;
        $this->email = $email;
        $this->gems = $gems;
        $this->enabled = $enabled;
    }
}
