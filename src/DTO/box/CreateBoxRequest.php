<?php

namespace App\DTO\box;

use App\Enum\BoxType;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateBoxRequest
{
    public string $name;
    public float $price;
    public int $type;
    public int $quantity;
    public int $brawler_quantity;
    public array $brawlers_in_box;

    public function __construct(string $name, float $price, int $type, int $quantity, int $brawler_quantity, array $brawlers_in_box)
    {
        $this->name = $name;
        $this->price = $price;
        $this->type = $type;
        $this->quantity = $quantity;
        $this->brawler_quantity = $brawler_quantity;
        $this->brawlers_in_box = $brawlers_in_box;
    }
}