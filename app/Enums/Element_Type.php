<?php

namespace App\Enums;

enum Element_Type: int
{
    case TEXT = 0;
    case INPUT = 1;
    case NUMBER = 2;
    case DATE = 3;
    case ATTACHED_IMAGE = 4; // صور
    case ATTACHED_FILE = 5;  // ملفات
    case Multiple_Choice = 6;
    case CHECKBOX = 7;
    case TITLE = 8;
}
