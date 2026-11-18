<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class BaseSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.settings';

    public ?array $data = [];

    abstract protected function fields(): array;

    abstract protected function editableKeys(): array;

    /** @return array<string, array<int, string>> */
    protected function tabKeys(): array
    {
        return [];
    }

    /** @return array<string, string> */
    protected function tabIcons(): array
    {
        return [];
    }

    /** @return array<string, array<int, \Filament\Schemas\Components\Component>> */
    protected function tabbedFields(): array
    {
        return collect($this->fields())->mapWithKeys(function ($section): array {
            $heading = $section instanceof Section ? ($section->getHeading() ?: 'Details') : 'Details';
            $actionName = 'save' . Str::studly($heading);

            return [$heading => [
                $section,
                Actions::make([
                    Action::make($actionName)
                        ->label("Save {$heading}")
                        ->icon('heroicon-m-check')
                        ->action(fn () => $this->saveTab($heading)),
                ])->alignEnd(),
            ]];
        })->all();
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $settings = array_replace(config('ughi.defaults'), SiteSetting::where('key', 'global')->first()?->data ?? []);
        $this->form->fill(array_intersect_key($settings, array_flip($this->editableKeys())));
    }

    public function form(Schema $schema): Schema
    {
        $icons = $this->tabIcons();

        $tabs = collect($this->tabbedFields())->map(function (array $components, string $label) use ($icons): Tab {
            return Tab::make($label)->icon($icons[$label] ?? null)->schema($components);
        })->values()->all();

        return $schema->components([
            Tabs::make(static::getNavigationLabel())->tabs($tabs)->vertical()->persistTabInQueryString('tab'),
        ])->statePath('data');
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);
        $state = array_intersect_key($this->form->getState(), array_flip($this->editableKeys()));
        $this->persistState($state);
        Notification::make()->title('Changes saved')->body('The website now reflects your changes.')->success()->send();
    }

    public function saveTab(string $tab): void
    {
        abort_unless(static::canAccess(), 403);

        $keys = $this->tabKeys()[$tab] ?? $this->editableKeys();
        $state = array_intersect_key($this->form->getState(), array_flip($keys));
        $this->persistState($state);

        Notification::make()->title("{$tab} saved")->body('The website now reflects your changes.')->success()->send();
    }

    /** @param array<string, mixed> $state */
    protected function persistState(array $state): void
    {
        DB::transaction(function () use ($state): void {
            $record = SiteSetting::where('key', 'global')->lockForUpdate()->firstOrFail();
            $record->update(['data' => array_replace($record->data, $state)]);
        });
    }
}
