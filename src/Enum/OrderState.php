<?php
namespace App\Enum;

enum OrderState: int
{
    case PENDING = 0;
    case PAID = 1;
    case RETURNED = 2;
    case CANCELLED = 3;
}