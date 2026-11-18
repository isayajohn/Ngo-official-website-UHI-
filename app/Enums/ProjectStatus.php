<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProjectStatus: string implements HasColor, HasLabel
{
    case Planned = 'PLANNED';
    case Active = 'ACTIVE';
    case OnHold = 'ON_HOLD';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';
    case Suspended = 'SUSPENDED';

    public function getLabel(): string
    {
        return ucfirst(strtolower($this->value));
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Planned => 'info', self::Active => 'success', self::OnHold, self::Suspended => 'warning', self::Completed => 'gray', self::Cancelled => 'danger'
        };
    }
}
