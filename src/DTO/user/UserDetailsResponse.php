<?php

namespace App\DTO\user;

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

    public function __construct()
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getBrawlTag(): string
    {
        return $this->brawlTag;
    }

    public function setBrawlTag(string $brawlTag): void
    {
        $this->brawlTag = $brawlTag;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): void
    {
        $this->surname = $surname;
    }

    public function getBirthDate(): string
    {
        return $this->birthDate;
    }

    public function setBirthDate(string $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    public function getDni(): string
    {
        return $this->dni;
    }

    public function setDni(string $dni): void
    {
        $this->dni = $dni;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getGems(): int
    {
        return $this->gems;
    }

    public function setGems(int $gems): void
    {
        $this->gems = $gems;
    }

    public function getTrophies(): int
    {
        return $this->trophies;
    }

    public function setTrophies(int $trophies): void
    {
        $this->trophies = $trophies;
    }

    public function getOpenBoxes(): int
    {
        return $this->openBoxes;
    }

    public function setOpenBoxes(int $openBoxes): void
    {
        $this->openBoxes = $openBoxes;
    }

    public function getFavouriteBrawlers(): int
    {
        return $this->favouriteBrawlers;
    }

    public function setFavouriteBrawlers(int $favouriteBrawlers): void
    {
        $this->favouriteBrawlers = $favouriteBrawlers;
    }

    public function isBrawlers(): bool
    {
        return $this->brawlers;
    }

    public function setBrawlers(bool $brawlers): void
    {
        $this->brawlers = $brawlers;
    }

    public function getGifts(): int
    {
        return $this->gifts;
    }

    public function setGifts(int $gifts): void
    {
        $this->gifts = $gifts;
    }
}
