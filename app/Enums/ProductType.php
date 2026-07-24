<?php
namespace App\Enums;

enum ProductType: string {
    case DELIVERY = 'delivery';
    case PICKUP   = 'pickup';
    case BOTH   = 'both';
}
