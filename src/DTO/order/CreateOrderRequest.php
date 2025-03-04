<?php

namespace App\DTO\order;

use App\DTO\box\CartItemRequest;
use Symfony\Component\Validator\Constraints as Assert;

class CreateOrderRequest
{

    /**
     * @var CartItemRequest[]
     */
    #[Assert\All([
        new Assert\Type(type: CartItemRequest::class),
    ])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public array $items;

    #[Assert\NotNull]
    public bool $useGems;

    #[Assert\NotNull]
    public bool $isGift;

    public string | null $giftUsername;

}