<?php

namespace App\Enums;

enum FormStatus: string
{
    case Active = 'فعالة';
    case Inactive = 'غير فعالة';
    case UNDER_REVIEW = 'قيد الدراسة';
}
