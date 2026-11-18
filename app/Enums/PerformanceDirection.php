<?php

namespace App\Enums;

enum PerformanceDirection: string
{
    case HigherIsBetter = 'HIGHER_IS_BETTER';
    case LowerIsBetter = 'LOWER_IS_BETTER';
    case TargetRange = 'TARGET_RANGE';
}
