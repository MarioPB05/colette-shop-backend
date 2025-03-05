<?php

namespace App\DTO\box;

use App\Enum\BoxType;
use Symfony\Contracts\Translation\TranslatorInterface;

class BoxCartResponse
{

    public int $id;
    public string $name;
    public string $type;
    public float $price;
    public int $quantity;
    public float $total_price;
    public int $boxes_left;
    public bool $is_daily;
    public int $claimed;

    public function __construct(int $id, string $name, string $type, float $price, int $quantity, float $total_price, int $boxes_left, bool $is_daily, int $claimed, TranslatorInterface $translator)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $translator->trans('BoxType.' . BoxType::tryFrom($type)->name, domain: 'enums');
        $this->price = $price;
        $this->quantity = $quantity;
        $this->total_price = $total_price;
        $this->boxes_left = $boxes_left;
        $this->is_daily = $is_daily;
        $this->claimed = $claimed;
    }

}