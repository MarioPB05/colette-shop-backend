<?php

namespace App\DTO\box;

use App\Enum\BoxType;
use Symfony\Contracts\Translation\TranslatorInterface;


class DailyBoxShopResponse
{
    public int $id;
    public string $name;
    public string $type;
    public int $favorite_brawlers_in_box;
    public int $repeat_every_hours;
    public bool $claimed;
    public string|null $last_claimed;

    public function __construct(int $id, string $name, string $type, int $favorite_brawlers_in_box, int $repeat_every_hours, bool $claimed, string|null $last_claimed, TranslatorInterface $translator)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $translator->trans('BoxType.' . BoxType::tryFrom($type)->name, domain: 'enums');
        $this->favorite_brawlers_in_box = $favorite_brawlers_in_box;
        $this->repeat_every_hours = $repeat_every_hours;
        $this->claimed = $claimed;
        $this->last_claimed = $last_claimed;
    }
}