<?php
namespace App\DTO\brawler;

class TableBrawlerResponse
{
    public int $id;
    public string $name;
    public int $numPeople;
    public int $numFavourite;
    public string $pinImage;
    public string $rarity;

    public function __construct(int $id, string $name, int $numPeople, int $numFavourite, string $pinImage, string $rarity)
    {
        $this->id = $id;
        $this->name = $name;
        $this->numPeople = $numPeople;
        $this->numFavourite = $numFavourite;
        $this->pinImage = $pinImage;
        $this->rarity = $rarity;
    }

}