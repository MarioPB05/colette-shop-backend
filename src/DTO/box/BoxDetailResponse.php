<?php

namespace App\DTO\box;

use App\Enum\BoxType;
use Symfony\Contracts\Translation\TranslatorInterface;

class BoxDetailResponse
{
    public int $id;
    public string $name;
    public float $price;
    public string $type;
    public int $boxes_left;
    public int $brawler_quantity;

    public function __construct(int $id, string $name, float $price, int $type, int $boxes_left, int $brawler_quantity, TranslatorInterface $translator)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->type = $translator->trans('BoxType.' . BoxType::tryFrom($type)->name, domain: 'enums');
        $this->boxes_left = $boxes_left;
        $this->brawler_quantity = $brawler_quantity;
    }
}