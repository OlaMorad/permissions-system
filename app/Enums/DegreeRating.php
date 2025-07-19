<?php

namespace App\Enums;

enum DegreeRating: string
{
    case Acceptable  = 'مقبول';
    case Good  = 'جيد';
    case VeryGood  = 'جيد جداً';
    case Excellent  = 'ممتاز';

    public static function fromDegree(?float $degree): ?self
    {
        if (is_null($degree)) {
            return null;
        }
        return match (true) {
            $degree >= 90 && $degree <= 100 => self::Excellent,
            $degree >= 80 => self::VeryGood,
            $degree >= 70 => self::Good,
            $degree >= 60 => self::Acceptable,
            default => null,
        };
    }
}
