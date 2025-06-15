<?php

namespace App\Enums;

enum StatusInternalMail: string
{
    case PENDING   = 'معلقة';
    case APPROVED  = 'موافقة';
    case REJECTED  = 'مرفوضة';
}
