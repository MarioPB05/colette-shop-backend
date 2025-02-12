<?php

namespace App\DTO\brawler;

class BrawlerProbabilityResponse
{

    public int $id;
    public string $name;
    public string $image;
    public int $rarity_id;
    public string $rarity;
    public bool $user_favorite;
    public float $probability;

    public function __construct(int $id, string $name, string $image, int $rarity_id, string $rarity, bool $user_favorite, float $probability)
    {
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->rarity_id = $rarity_id;
        $this->rarity = $rarity;
        $this->user_favorite = $user_favorite;
        $this->probability = $probability;
    }

}