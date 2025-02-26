<?php

namespace App\DTO\brawler;

class InventoryBrawlerResponse
{
    public int $id;
    public string $name;
    public string $image;
    public int $user_quantity_actual;
    public int $user_quantity_past;

    public function __construct(int $id, string $name, string $image, int $user_quantity_actual, int $user_quantity_past)
    {
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->user_quantity_actual = $user_quantity_actual;
        $this->user_quantity_past = $user_quantity_past;
    }
}