<?php

namespace App\DTO\brawler;

class UserBrawlerProbabilityResponse
{
    public int $id;
    public string $name;
    public string $image;
    public string $model_image;
    public int $probability;
    public int $user_quantity;
    public int $rarity_id;

    public function __construct(int $id, string $name, string $image, string $model_image, int $probability, int $user_quantity, int $rarity_id)
    {
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->model_image = $model_image;
        $this->probability = $probability;
        $this->user_quantity = $user_quantity;
        $this->rarity_id = $rarity_id;
    }
}