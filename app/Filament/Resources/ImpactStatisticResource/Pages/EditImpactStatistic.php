<?php

namespace App\Filament\Resources\ImpactStatisticResource\Pages;

use App\Filament\Resources\ImpactStatisticResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditImpactStatistic extends EditRecord
{
    protected static string $resource = ImpactStatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verify')->label('Verify saved indicator')->requiresConfirmation()
                ->modalDescription('Confirm that the saved figure, reporting date and source have been checked. Unsaved form changes are not included.')
                ->authorize(fn () => auth()->user()->can('verify', $this->record))
                ->visible(fn () => ! $this->record->verified_at && ! $this->record->trashed())
                ->action(function () {
                    $this->record->verify(auth()->user());
                    Notification::make()->title('Indicator verified. It may now be published.')->success()->send();
                }),
            DeleteAction::make(), RestoreAction::make(),
        ];
    }
}
