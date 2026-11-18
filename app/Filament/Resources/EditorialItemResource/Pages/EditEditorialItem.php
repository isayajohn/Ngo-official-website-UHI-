<?php

namespace App\Filament\Resources\EditorialItemResource\Pages;

use App\Filament\Resources\EditorialItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEditorialItem extends EditRecord
{
    protected static string $resource = EditorialItemResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
