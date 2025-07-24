<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PAID = 'مدفوع';
    case PENDING = 'غير مدفوع';
}
