<?php

namespace App\Entity;

use App\Repository\BrawlerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'brawler')]
#[ORM\Entity(repositoryClass: BrawlerRepository::class)]
class Brawler
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $name = null;

    #[ORM\Column(length: 1000)]
    private ?string $image = null;

    #[ORM\Column(length: 1000)]
    private ?string $pin_image = null;

    #[ORM\Column(length: 1000)]
    private ?string $model_image = null;

    #[ORM\Column(length: 1000)]
    private ?string $portrait_image = null;

    #[ORM\ManyToOne(inversedBy: 'brawlers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rarity $rarity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getPinImage(): ?string
    {
        return $this->pin_image;
    }

    public function setPinImage(string $pin_image): static
    {
        $this->pin_image = $pin_image;

        return $this;
    }

    public function getModelImage(): ?string
    {
        return $this->model_image;
    }

    public function setModelImage(string $model_image): static
    {
        $this->model_image = $model_image;

        return $this;
    }

    public function getPortraitImage(): ?string
    {
        return $this->portrait_image;
    }

    public function setPortraitImage(string $portrait_image): static
    {
        $this->portrait_image = $portrait_image;

        return $this;
    }

    public function getRarity(): ?Rarity
    {
        return $this->rarity;
    }

    public function setRarity(?Rarity $rarity): static
    {
        $this->rarity = $rarity;

        return $this;
    }
}
