<?php

namespace App\DTO\user;

use App\DTO\brawler\UserBrawler;

class UserDetailsResponse
{
    public int $id;
    public string $username;
    public string $brawlTag;
    public string $name;
    public string $surname;
    public string $birthDate;
    public string $dni;
    public string $email;
    public int $gems;
    public int $trophies; // brawlers that the user has repeated
    public int $openBoxes;
    public int $favouriteBrawlers;
    public int $brawlers; // the number of brawlers that the user has
    public int $gifts; // the number of gifts that the user sends
    public UserBrawler $brawlerAvatar;

    public function __construct()
    {
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setBrawlTag(string $brawlTag): void
    {
        $this->brawlTag = $brawlTag;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setSurname(string $surname): void
    {
        $this->surname = $surname;
    }

    public function setBirthDate(string $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    public function setDni(string $dni): void
    {
        $this->dni = $dni;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setGems(int $gems): void
    {
        $this->gems = $gems;
    }

    public function setTrophies(int $trophies): void
    {
        $this->trophies = $trophies;
    }

    public function setOpenBoxes(int $openBoxes): void
    {
        $this->openBoxes = $openBoxes;
    }

    public function setFavouriteBrawlers(int $favouriteBrawlers): void
    {
        $this->favouriteBrawlers = $favouriteBrawlers;
    }

    public function setBrawlers(int $brawlers): void
    {
        $this->brawlers = $brawlers;
    }

    public function setGifts(int $gifts): void
    {
        $this->gifts = $gifts;
    }

    public function setBrawlerAvatar(string $brawlerAvatar): void
    {
        $this->brawlerAvatar = $brawlerAvatar;
    }

    public function setBrawlerAvatarImage($brawleravatar_image): void
    {
        $this->brawlerAvatarImage = $brawleravatar_image;
    }
}
