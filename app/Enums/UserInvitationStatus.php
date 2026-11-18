<?php

namespace App\Enums;

enum UserInvitationStatus: string
{
    case Pending = 'PENDING';
    case Accepted = 'ACCEPTED';
    case Expired = 'EXPIRED';
    case Revoked = 'REVOKED';
    case Cancelled = 'CANCELLED';
}
