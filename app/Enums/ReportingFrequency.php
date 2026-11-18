<?php

namespace App\Enums;

enum ReportingFrequency: string
{
    case Monthly = 'MONTHLY';
    case Quarterly = 'QUARTERLY';
    case SemiAnnual = 'SEMI_ANNUAL';
    case Annual = 'ANNUAL';
    case ProjectBased = 'PROJECT_BASED';
    case Custom = 'CUSTOM';
}
