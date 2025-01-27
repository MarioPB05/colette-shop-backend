<?php

namespace App\Entity;

use App\Repository\RarityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'rarity')]
#[ORM\Entity(repositoryClass: RarityRepository::class)]
class Rarity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $color = null;

    /**
     * @var Collection<int, Brawler>
     */
    #[ORM\OneToMany(targetEntity: Brawler::class, mappedBy: 'rarity')]
    private Collection $brawlers;

    public function __construct()
    {
        $this->brawlers = new ArrayCollection();
    }

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection<int, Brawler>
     */
    public function getBrawlers(): Collection
    {
        return $this->brawlers;
    }

    public function addBrawler(Brawler $brawler): static
    {
        if (!$this->brawlers->contains($brawler)) {
            $this->brawlers->add($brawler);
            $brawler->setRarity($this);
        }

        return $this;
    }

    public function removeBrawler(Brawler $brawler): static
    {
        if ($this->brawlers->removeElement($brawler)) {
            // set the owning side to null (unless already changed)
            if ($brawler->getRarity() === $this) {
                $brawler->setRarity(null);
            }
        }

        return $this;
    }
}
