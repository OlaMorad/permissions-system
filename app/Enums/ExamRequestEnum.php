<?php
namespace App\Enums;

enum ExamRequestEnum: string
{
    case PENDING   = 'قيد الدراسة';
    case APPROVED  = 'مقبول';
    case REJECTED  = 'مرفوض';
}
