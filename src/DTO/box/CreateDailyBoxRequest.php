<?php

namespace App\DTO\box;

class CreateDailyBoxRequest
{
    public string $name;
    public int $type;
    public int $repeat_every_hours;
    public int $brawler_quantity;
    public array $brawlers_in_box;

    public function __construct(string $name, int $type, int $repeat_every_hours, int $brawler_quantity, array $brawlers_in_box)
    {
        $this->name = $name;
        $this->type = $type;
        $this->repeat_every_hours = $repeat_every_hours;
        $this->brawler_quantity = $brawler_quantity;
        $this->brawlers_in_box = $brawlers_in_box;
    }
}