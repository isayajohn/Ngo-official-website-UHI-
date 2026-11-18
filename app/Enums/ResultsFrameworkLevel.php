<?php

namespace App\Enums;

enum ResultsFrameworkLevel: string
{
    case Impact = 'IMPACT';
    case Outcome = 'OUTCOME';
    case Output = 'OUTPUT';
    case Activity = 'ACTIVITY';
}
