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

    public function __construct(int $id, string $name, float $price, int $quantity, BoxType $type, bool $pinned, TranslatorInterface $translator)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->type = $translator->trans('BoxType.' . $type->name, domain: 'enums');
        $this->pinned = $pinned;
    }

}