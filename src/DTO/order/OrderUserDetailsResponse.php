<?php

namespace App\DTO\order;

class OrderUserDetailsResponse
{

    public int $id;
    public string $invoiceNumber;
    public string $purchaseDate;
    public string $username;
    public int $totalItems;
    public float $discount;
    public float $totalPrice;
    public float $totalWithDiscount;
    public ?string $giftUsername;

    public function __construct()
    {
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setInvoiceNumber(string $invoiceNumber): void
    {
        $this->invoiceNumber = $invoiceNumber;
    }

    public function setPurchaseDate(string $purchaseDate): void
    {
        $this->purchaseDate = $purchaseDate;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setTotalItems(int $totalItems): void
    {
        $this->totalItems = $totalItems;
    }

    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }

    public function setTotalPrice(float $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    public function setTotalWithDiscount(float $totalWithDiscount): void
    {
        $this->totalWithDiscount = $totalWithDiscount;
    }

    public function setGiftUsername(?string $giftUsername): void
    {
        $this->giftUsername = $giftUsername;
    }
}