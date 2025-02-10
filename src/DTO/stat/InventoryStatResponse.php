<?php

namespace App\DTO\stat;

class InventoryStatResponse
{
    public string $day;
    public int $boxes;
    public float $totalPrice;

    public function __construct(string $day, int $boxes, float $totalPrice)
    {
        $this->day = $day;
        $this->boxes = $boxes;
        $this->totalPrice = $totalPrice;
    }
}
