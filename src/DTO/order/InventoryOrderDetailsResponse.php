<?php

namespace App\DTO\order;

class InventoryOrderDetailsResponse
{
    public int $id;
    public string $price;
    public string $collectDate;
    public string $openDate;
    public string $boxName;
    public string $boxType;

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

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): void
    {
        $this->price = $price;
    }

    public function getCollectDate(): string
    {
        return $this->collectDate;
    }

    public function setCollectDate(string $collectDate): void
    {
        $this->collectDate = $collectDate;
    }

    public function getOpenDate(): string
    {
        return $this->openDate;
    }

    public function setOpenDate(string $openDate): void
    {
        $this->openDate = $openDate;
    }

    public function getBoxName(): string
    {
        return $this->boxName;
    }

    public function setBoxName(string $boxName): void
    {
        $this->boxName = $boxName;
    }

    public function getBoxType(): string
    {
        return $this->boxType;
    }

    public function setBoxType(string $boxType): void
    {
        $this->boxType = $boxType;
    }



}
