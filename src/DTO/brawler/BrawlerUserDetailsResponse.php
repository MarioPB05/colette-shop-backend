<?php

namespace App\DTO\brawler;

class BrawlerUserDetailsResponse
{
public int $brawlerId;
public string $name;
public string $image;
public string $modelImage;

public function __construct()
{
}

    public function setBrawlerId(int $brawlerId): void
    {
        $this->brawlerId = $brawlerId;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function setModelImage(string $modelImage): void
    {
        $this->modelImage = $modelImage;
    }

}
