<?php

namespace App\DTO\box;

use App\Enum\BoxType;
use Symfony\Contracts\Translation\TranslatorInterface;

class TableBoxResponse
{

    public int $id;

    public string $name;

    public float $price;

    public int $quantity;

    public string $type;

    public bool $pinned;
    public bool $isDaily;

    public function __construct(int $id, string $name, float $price, int $quantity, int $type, bool $pinned, bool $isDaily, TranslatorInterface $translator)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->type = $translator->trans('BoxType.' . BoxType::from($type)->name, domain: 'enums');
        $this->pinned = $pinned;
        $this->isDaily = $isDaily;
    }
}