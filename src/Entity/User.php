<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: '`user`')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $username = null;

    #[ORM\Column(length: 500)]
    private ?string $password = null;

    #[ORM\Column(length: 200)]
    private ?string $email = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $gems = 0;

    #[ORM\Column(length: 9, options: ['fixed' => true])]
    private ?string $brawl_tag = null;

    #[ORM\Column]
    private bool $enabled = true;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\Column(type: 'integer', enumType: UserRole::class)]
    private UserRole $role;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'brawler_avatar', nullable: true)]
    private ?Brawler $brawler_avatar = null;

    /**
     * @var Collection<int, Brawler>
     */
    #[ORM\ManyToMany(targetEntity: Brawler::class)]
    #[ORM\JoinTable(
        name: 'user_favorite_brawlers',
        joinColumns: [
            new Orm\JoinColumn(name: 'user_id', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new Orm\JoinColumn(name: 'brawler_id', referencedColumnName: 'id')
        ]
    )]
    private Collection $favorite_brawlers;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orders;

    /**
     * @var Collection<int, Inventory>
     */
    #[ORM\OneToMany(targetEntity: Inventory::class, mappedBy: 'user')]
    private Collection $inventory;

    /**
     * @var Collection<int, UserBrawler>
     */
    #[ORM\OneToMany(targetEntity: UserBrawler::class, mappedBy: 'user')]
    private Collection $userBrawlers;

    public function __construct()
    {
        $this->favorite_brawlers = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->inventory = new ArrayCollection();
        $this->userBrawlers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getGems(): int
    {
        return $this->gems;
    }

    public function setGems(int $gems): static
    {
        $this->gems = $gems;

        return $this;
    }

    public function getBrawlTag(): ?string
    {
        return $this->brawl_tag;
    }

    public function setBrawlTag(string $brawl_tag): static
    {
        $this->brawl_tag = $brawl_tag;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getBrawlerAvatar(): ?Brawler
    {
        return $this->brawler_avatar;
    }

    public function setBrawlerAvatar(?Brawler $brawler_avatar): static
    {
        $this->brawler_avatar = $brawler_avatar;

        return $this;
    }

    /**
     * @return Collection<int, Brawler>
     */
    public function getFavoriteBrawlers(): Collection
    {
        return $this->favorite_brawlers;
    }

    public function addFavoriteBrawler(Brawler $favoriteBrawler): static
    {
        if (!$this->favorite_brawlers->contains($favoriteBrawler)) {
            $this->favorite_brawlers->add($favoriteBrawler);
        }

        return $this;
    }

    public function removeFavoriteBrawler(Brawler $favoriteBrawler): static
    {
        $this->favorite_brawlers->removeElement($favoriteBrawler);

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Inventory>
     */
    public function getInventory(): Collection
    {
        return $this->inventory;
    }

    public function addInventory(Inventory $inventory): static
    {
        if (!$this->inventory->contains($inventory)) {
            $this->inventory->add($inventory);
            $inventory->setUser($this);
        }

        return $this;
    }

    public function removeInventory(Inventory $inventory): static
    {
        if ($this->inventory->removeElement($inventory)) {
            // set the owning side to null (unless already changed)
            if ($inventory->getUser() === $this) {
                $inventory->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserBrawler>
     */
    public function getUserBrawlers(): Collection
    {
        return $this->userBrawlers;
    }

    public function addUserBrawler(UserBrawler $userBrawler): static
    {
        if (!$this->userBrawlers->contains($userBrawler)) {
            $this->userBrawlers->add($userBrawler);
            $userBrawler->setUser($this);
        }

        return $this;
    }

    public function removeUserBrawler(UserBrawler $userBrawler): static
    {
        if ($this->userBrawlers->removeElement($userBrawler)) {
            // set the owning side to null (unless already changed)
            if ($userBrawler->getUser() === $this) {
                $userBrawler->setUser(null);
            }
        }

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_' . $this->getRole()->name];
    }

    public function eraseCredentials(): void {}

    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
