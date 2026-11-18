<?php

namespace App\Enums;

enum IndicatorCalculationType: string
{
    case Sum = 'SUM';
    case Count = 'COUNT';
    case Percentage = 'PERCENTAGE';
    case Rate = 'RATE';
    case Average = 'AVERAGE';
    case Manual = 'MANUAL';
}
