<?php

namespace App\Enums;


enum QuestionBankEnum: String
{
    case PENDING   = 'قيد الدراسة';
    case APPROVED  = 'مقبول';
    case REJECTED  = 'مرفوض';
}
