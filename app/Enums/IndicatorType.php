<?php

namespace App\Enums;

enum IndicatorType: string
{
    case Input = 'INPUT';
    case Process = 'PROCESS';
    case Output = 'OUTPUT';
    case Outcome = 'OUTCOME';
    case Impact = 'IMPACT';
}
