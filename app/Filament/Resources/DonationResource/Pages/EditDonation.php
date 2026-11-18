<?php

namespace App\Filament\Resources\DonationResource\Pages;

use App\Filament\Resources\DonationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDonation extends EditRecord
{
    protected static string $resource = DonationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === 'RECEIVED' && empty($data['received_at'])) {
            $data['received_at'] = now();
        }

        if (($data['status'] ?? null) !== 'RECEIVED') {
            $data['received_at'] = null;
        }

        return $data;
    }
}
