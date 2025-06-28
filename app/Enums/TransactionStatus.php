<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case PENDING = 'انتظار';
    case FORWARDED = 'محول';
    case REJECTED = 'مرفوض';
    case LATE = 'متأخرة';
    case COMPLETED = 'منجزة';
    case UNDER_REVIEW = 'قيد الدراسة';
}
