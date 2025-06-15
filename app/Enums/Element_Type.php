<?php

namespace App\Enums;

enum Element_Type :int
{
    case TEXT = 0;
    case INPUT = 1;
    case NUMBER = 2;
    case DATE = 3;
    case EMAIL = 4;
    case SELECT = 5;
    case CHECKBOX = 6;
}
