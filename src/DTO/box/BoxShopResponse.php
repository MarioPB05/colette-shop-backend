<?php

namespace App\DTO\box;

use App\Enum\BoxType;
use Symfony\Contracts\Translation\TranslatorInterface;

class BoxShopResponse
{
    public int $id;
    public string $name;
    public float $price;
    public string $type;
    public int $boxes_left;
    public int $favorite_brawlers_in_box;
    public bool $pinned;
    public bool $popular;

    public function __construct(int $id, string $name, float $price, int $type, int $boxes_left, int $favorite_brawlers_in_box, bool $pinned, bool $popular, TranslatorInterface $translator)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->type = $translator->trans('BoxType.' . BoxType::tryFrom($type)->name, domain: 'enums');
        $this->boxes_left = $boxes_left;
        $this->favorite_brawlers_in_box = $favorite_brawlers_in_box;
        $this->pinned = $pinned;
        $this->popular = $popular;
    }
}