<?php

namespace App\DTO\order;

use App\Enum\OrderState;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderDetailsResponse
{
    public OrderParticipantResponse $from;
    public OrderParticipantResponse $to;
    public float $total;
    public float $discount;
    public int $gems;
    public string $invoiceNumber;
    public string $purchaseDate;
    public string $state;

    /**
     * @var InventoryOrderDetailsResponse[]
     */
    public array $inventory;

    public function __construct() {
    }

    public function getFrom(): OrderParticipantResponse
    {
        return $this->from;
    }

    public function setFrom(OrderParticipantResponse $from): void
    {
        $this->from = $from;
    }

    public function setTo(OrderParticipantResponse $to): void
    {
        $this->to = $to;
    }

    public function getSubTotal(): float
    {
        return $this->subTotal;
    }

    public function setSubTotal(float $subTotal): void
    {
        $this->subTotal = $subTotal;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function setTotal(float $total): void
    {
        $this->total = $total;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }

    public function getGems(): int
    {
        return $this->gems;
    }

    public function setGems(int $gems): void
    {
        $this->gems = $gems;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): void
    {
        $this->invoiceNumber = $invoiceNumber;
    }

    public function getPurchaseDate(): string
    {
        return $this->purchaseDate;
    }

    public function setPurchaseDate(string $purchaseDate): void
    {
        $this->purchaseDate = $purchaseDate;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getInventory(): array
    {
        return $this->inventory;
    }

    public function setInventory(array $inventory): void
    {
        $this->inventory = $inventory;
    }


}

