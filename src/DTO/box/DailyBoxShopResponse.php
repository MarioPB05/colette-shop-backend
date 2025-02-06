<?php

namespace App\DTO\box;

use App\Enum\BoxType;
use Symfony\Contracts\Translation\TranslatorInterface;


class DailyBoxShopResponse
{
    public int $id;
    public string $name;
    public string $type;
    public int $favoriteBrawlersInBox;
    public int $repeatHours;
    public bool $claimed;

    public function __construct(int $id, string $name, string $type, int $favoriteBrawlersInBox, int $repeatHours, bool $claimed, TranslatorInterface $translator)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $translator->trans('BoxType.' . BoxType::tryFrom($type)->name, domain: 'enums');
        $this->favoriteBrawlersInBox = $favoriteBrawlersInBox;
        $this->repeatHours = $repeatHours;
        $this->claimed = $claimed;
    }
}