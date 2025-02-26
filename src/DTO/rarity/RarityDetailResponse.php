<?php

namespace App\DTO\rarity;

class RarityDetailResponse
{
    public int $id;
    public string $name;
    public string $color;
    public int $brawlers_of_rarity_unlocked;
    public int $total_brawlers_of_rarity;

    public function __construct(int $id, string $name, string $color, int $brawlers_of_rarity_unlocked, int $total_brawlers_of_rarity)
    {
        $this->id = $id;
        $this->name = $name;
        $this->color = $color;
        $this->brawlers_of_rarity_unlocked = $brawlers_of_rarity_unlocked;
        $this->total_brawlers_of_rarity = $total_brawlers_of_rarity;
    }
}