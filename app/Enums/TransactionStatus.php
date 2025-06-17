<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case PENDING = 'انتظار';
    case LATE = 'متأخرة';
    case COMPLETED = 'منجزة';
    case CANCELED = 'ملغاة';
    case UNDER_REVIEW = 'تحت الدراسة';
}
