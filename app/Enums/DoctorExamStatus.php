<?php

namespace App\Enums;

enum DoctorExamStatus: string
{
    case Passed = 'ناجح';
    case Failed = 'راسب';

    public static function fromDegree(?float $degree): ?self
    {
        if (is_null($degree)) {
            return null;
        }
        return $degree >= 60 ? self::Passed : self::Failed;
    }
}
