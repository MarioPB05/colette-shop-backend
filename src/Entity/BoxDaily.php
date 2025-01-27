<?php

namespace App\Entity;

use App\Repository\BoxDailyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'box_daily')]
#[ORM\Entity(repositoryClass: BoxDailyRepository::class)]
class BoxDaily
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $repeat_every_hours = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Box $box = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepeatEveryHours(): ?int
    {
        return $this->repeat_every_hours;
    }

    public function setRepeatEveryHours(int $repeat_every_hours): static
    {
        $this->repeat_every_hours = $repeat_every_hours;

        return $this;
    }

    public function getBox(): ?Box
    {
        return $this->box;
    }

    public function setBox(Box $box): static
    {
        $this->box = $box;

        return $this;
    }
}
