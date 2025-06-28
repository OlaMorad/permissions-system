<?php

namespace App\Enums;

enum StatusInternalMail: string
{
    case PENDING   = 'قيد الدراسة';
    case APPROVED  = 'مرسلة';
    case REJECTED  = 'مرفوضة';
}
