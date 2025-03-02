<?php

namespace App\DTO\brawler;

class UserBrawler
{
    public int $id;
    public string $image;
    public string $pinImage;
    public string $modelImage;
    public string $portraitImage;
    public string $name;

    public function __construct()
    {
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function setPinImage(string $pinImage): void
    {
        $this->pinImage = $pinImage;
    }

    public function setModelImage(string $modelImage): void
    {
        $this->modelImage = $modelImage;
    }

    public function setPortraitImage(string $portraitImage): void
    {
        $this->portraitImage = $portraitImage;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

}
