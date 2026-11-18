<?php

namespace App\Enums;

enum ReportingPeriodStatus: string
{
    case Upcoming = 'UPCOMING';
    case Open = 'OPEN';
    case DataEntryClosed = 'DATA_ENTRY_CLOSED';
    case UnderVerification = 'UNDER_VERIFICATION';
    case Closed = 'CLOSED';
    case Archived = 'ARCHIVED';
}
