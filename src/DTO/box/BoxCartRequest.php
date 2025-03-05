<?php

namespace App\DTO\box;

class BoxCartRequest
{

    /**
     * @var CartItemRequest[]
     */
    public array $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

}
