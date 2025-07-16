<?php

namespace App\Enums;

enum Program_ExamStatus: string
{
    case PENDING = 'انتظار';
    case ACTIVE = 'فعال';
    case FINISHED = 'انتهى';
}
