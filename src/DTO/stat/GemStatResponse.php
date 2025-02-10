<?php

namespace App\DTO\stat;

class GemStatResponse
{
    public string $day;
    public int $gems;

    public function __construct(string $day, int $gems)
    {
        $this->day = $day;
        $this->gems = $gems;
    }
}
