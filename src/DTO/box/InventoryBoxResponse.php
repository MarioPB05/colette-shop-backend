<?php

namespace App\DTO\box;

use App\Enum\BoxType;
use Symfony\Contracts\Translation\TranslatorInterface;

class InventoryBoxResponse
{
    public int $id;
    public int $box_id;
    public string $type;
    public int $brawler_quantity;
    public bool $open;

    public function __construct(int $id, int $box_id, BoxType $type, int $brawler_quantity, bool $open, TranslatorInterface $translator)
    {
        $this->id = $id;
        $this->box_id = $box_id;
        $this->type = $translator->trans('BoxType.' . $type->name, domain: 'enums');
        $this->brawler_quantity = $brawler_quantity;
        $this->open = $open;
    }
}