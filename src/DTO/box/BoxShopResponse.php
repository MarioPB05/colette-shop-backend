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
    public int $boxesLeft;
    public int $favoriteBrawlersInBox;
    public bool $pinned;
    public bool $popular;

    public function __construct(int $id, string $name, float $price, BoxType $type, int $boxesLeft, int $favoriteBrawlersInBox, bool $pinned, bool $popular, TranslatorInterface $translator)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->type = $translator->trans('BoxType.' . $type->name, domain: 'enums');
        $this->boxesLeft = $boxesLeft;
        $this->favoriteBrawlersInBox = $favoriteBrawlersInBox;
        $this->pinned = $pinned;
        $this->popular = $popular;
    }
}