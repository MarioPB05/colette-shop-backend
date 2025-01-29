<?php

namespace App\DTO\box;

use App\Enum\BoxType;

class AdminGetBoxesResponse
{

    public int $id;

    public string $name;

    public float $price;

    public int $quantity;

    public string $type;

    public bool $pinned;

    public function __construct(int $id, string $name, float $price, int $quantity, BoxType $type, bool $pinned)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->type =$type->name;
        $this->pinned = $pinned;
    }

}