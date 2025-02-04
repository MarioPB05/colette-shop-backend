<?php

namespace App\Entity;

use App\Enum\BoxType;
use App\Repository\BoxRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'box')]
#[ORM\Entity(repositoryClass: BoxRepository::class)]
class Box
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(options: ['default' => 5])]
    private int $brawler_quantity = 5;

    #[ORM\Column(options: ['default' => false])]
    private bool $deleted = false;

    #[ORM\Column(type: 'integer', enumType: BoxType::class)]
    private BoxType $type;

    #[ORM\Column(options: ['default' => false])]
    private bool $pinned = false;

    /**
     * @var Collection<int, BoxReview>
     */
    #[ORM\OneToMany(targetEntity: BoxReview::class, mappedBy: 'box')]
    private Collection $boxReviews;

    public function __construct()
    {
        $this->boxReviews = new ArrayCollection();
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getBrawlerQuantity(): int
    {
        return $this->brawler_quantity;
    }

    public function setBrawlerQuantity(int $brawler_quantity): static
    {
        $this->brawler_quantity = $brawler_quantity;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getType(): BoxType
    {
        return $this->type;
    }

    public function setType(BoxType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function isPinned(): bool
    {
        return $this->pinned;
    }

    public function setPinned(bool $pinned): static
    {
        $this->pinned = $pinned;

        return $this;
    }

    /**
     * @return Collection<int, BoxReview>
     */
    public function getBoxReviews(): Collection
    {
        return $this->boxReviews;
    }

    public function addBoxReview(BoxReview $boxReview): static
    {
        if (!$this->boxReviews->contains($boxReview)) {
            $this->boxReviews->add($boxReview);
            $boxReview->setBox($this);
        }

        return $this;
    }

    public function removeBoxReview(BoxReview $boxReview): static
    {
        if ($this->boxReviews->removeElement($boxReview)) {
            // set the owning side to null (unless already changed)
            if ($boxReview->getBox() === $this) {
                $boxReview->setBox(null);
            }
        }

        return $this;
    }
}
