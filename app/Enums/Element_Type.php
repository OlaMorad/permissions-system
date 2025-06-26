<?php

namespace App\Enums;

enum Element_Type: int
{
    case TEXT = 0;
    case INPUT = 1;
    case NUMBER = 2;
    case DATE = 3;
    case attached  = 4;
    case Multiple_Choice = 5;
    case CHECKBOX = 6;
}
