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
    #[Assert\NotBlank]
    public array $items;

    #[Assert\NotBlank]
    public bool $useGems;

    #[Assert\NotBlank]
    public bool $isGift;

    public string | null $giftUsername;

}