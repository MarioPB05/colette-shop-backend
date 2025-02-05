<?php

namespace App\DTO\order;

use App\Enum\OrderState;
use Symfony\Contracts\Translation\TranslatorInterface;

class TableOrderResponse
{
    public string $invoice_number;
    public string $purchase_date;
    public string $state;
    public string $username;
    public string $user_image;
    public float $discount;
    public float $total_price;
    public float $total_with_discount;

    public function __construct(
        string              $invoice_number,
        string              $purchase_date,
        string              $state,
        string              $username,
        string              $user_image,
        float               $discount,
        float               $total_price,
        float               $total_with_discount,
        TranslatorInterface $translator
    )
    {
        $this->invoice_number = $invoice_number;
        $this->purchase_date = $purchase_date;
        $this->state = $translator->trans('OrderState.' . OrderState::tryFrom($state)->name, domain: 'enums');
        $this->username = $username;
        $this->user_image = $user_image;
        $this->discount = $discount;
        $this->total_price = $total_price;
        $this->total_with_discount = $total_with_discount;
    }
}