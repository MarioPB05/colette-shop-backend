<?php

namespace App\DTO\brawler;

class BrawlerCardResponse
{
    public int $id;
    public string $name;
    public string $model_image;
    public int $rarity_id;
    public string $rarity_color;
    public int $user_quantity;
    public bool $user_favorite;

    public function __construct(int $id, string $name, string $model_image, int $rarity_id, string $rarity_color, int $user_quantity, bool $user_favorite)
    {
        $this->id = $id;
        $this->name = $name;
        $this->model_image = $model_image;
        $this->rarity_id = $rarity_id;
        $this->rarity_color = $rarity_color;
        $this->user_quantity = $user_quantity;
        $this->user_favorite = $user_favorite;
    }
}