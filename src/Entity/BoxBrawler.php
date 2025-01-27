<?php

namespace App\Entity;

use App\Repository\BoxBrawlerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BoxBrawlerRepository::class)]
class BoxBrawler
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Box $box = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Brawler $brawler = null;

    #[ORM\Column]
    private ?float $probability = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBox(): ?Box
    {
        return $this->box;
    }

    public function setBox(?Box $box): static
    {
        $this->box = $box;

        return $this;
    }

    public function getBrawler(): ?Brawler
    {
        return $this->brawler;
    }

    public function setBrawler(?Brawler $brawler): static
    {
        $this->brawler = $brawler;

        return $this;
    }

    public function getProbability(): ?float
    {
        return $this->probability;
    }

    public function setProbability(float $probability): static
    {
        $this->probability = $probability;

        return $this;
    }
}
