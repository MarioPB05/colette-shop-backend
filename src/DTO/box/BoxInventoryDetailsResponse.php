<?php

namespace App\DTO\box;

class BoxInventoryDetailsResponse
{
    public int $inventoryId;
    public bool $open;
    public string $collectDate;
    public ?string $openDate;
    public int $boxId;
    public string $boxName;
    public int $totalBrawlers;
    public int $newBrawlersObtained;
    public int $totalTrophies;
    public ?string $giftFrom;

    public function __construct()
    {
    }

    public function setInventoryId(int $inventoryId): void
    {
        $this->inventoryId = $inventoryId;
    }

    public function setOpen(bool $open): void
    {
        $this->open = $open;
    }

    public function setCollectDate(string $collectDate): void
    {
        $this->collectDate = $collectDate;
    }

    public function setBoxName(string $boxName): void
    {
        $this->boxName = $boxName;
    }

    public function setTotalBrawlers(int $totalBrawlers): void
    {
        $this->totalBrawlers = $totalBrawlers;
    }

    public function setNewBrawlersObtained(int $newBrawlersObtained): void
    {
        $this->newBrawlersObtained = $newBrawlersObtained;
    }

    public function setTotalTrophies(int $totalTrophies): void
    {
        $this->totalTrophies = $totalTrophies;
    }

    public function setGiftFrom(?string $giftFrom): void
    {
        $this->giftFrom = $giftFrom;
    }

    public function setBoxId(int $boxId): void
    {
        $this->boxId = $boxId;
    }

    public function setOpenDate(string $openDate): void
    {
        $this->openDate = $openDate;
    }
}