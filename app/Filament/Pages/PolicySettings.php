<?php

namespace App\Filament\Pages;

use App\Models\ContentPage;
use App\Models\SiteSetting;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\DB;

class PolicySettings extends BaseSettings
{
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Policy Pages';
    protected static ?string $title = 'Policy Pages';

    private const PAGES = [
        'privacy-policy' => ['privacy_policy_title', 'privacy_policy_description', 'privacy_policy_body'],
        'safeguarding' => ['safeguarding_title', 'safeguarding_description', 'safeguarding_body'],
        'terms' => ['terms_title', 'terms_description', 'terms_body'],
    ];

    protected function tabKeys(): array
    {
        return ['Privacy Policy' => self::PAGES['privacy-policy'], 'Safeguarding' => self::PAGES['safeguarding'], 'Terms' => self::PAGES['terms']];
    }

    protected function tabIcons(): array
    {
        return ['Privacy Policy' => 'heroicon-o-lock-closed', 'Safeguarding' => 'heroicon-o-shield-check', 'Terms' => 'heroicon-o-document-text'];
    }

    public static function canAccess(): bool
    {
        return (auth()->user()?->can('settings.manage') ?? false) || (auth()->user()?->can('safeguarding.update') ?? false);
    }

    protected function editableKeys(): array
    {
        $user = auth()->user();

        return array_merge(
            $user?->can('settings.manage') ? [...self::PAGES['privacy-policy'], ...self::PAGES['terms']] : [],
            $user?->can('safeguarding.update') ? self::PAGES['safeguarding'] : [],
        );
    }

    public function mount(): void
    {
        parent::mount();
        $user = auth()->user();
        foreach (self::PAGES as $path => [$titleKey, $descriptionKey, $bodyKey]) {
            $allowed = in_array($path, ['privacy-policy', 'terms']) ? $user?->can('settings.manage') : $user?->can('safeguarding.update');
            if (! $allowed) continue;

            $page = ContentPage::where('path', $path)->first();
            if (! $page) continue;
            $this->data[$titleKey] = $this->data[$titleKey] ?? $page->translated('title');
            $this->data[$descriptionKey] = $this->data[$descriptionKey] ?? $page->translated('description');
            $this->data[$bodyKey] = $this->data[$bodyKey] ?? $page->translated('body');
        }
        $this->form->fill($this->data);
    }

    protected function fields(): array
    {
        $section = function (string $title, array $keys, string $description, \Closure $visible): Section {
            [$titleKey, $descriptionKey, $bodyKey] = $keys;

            return Section::make($title)->description($description)->schema([
                TextInput::make($titleKey)->label('Page title')->required()->maxLength(255),
                Textarea::make($descriptionKey)->label('Introduction')->rows(3)->maxLength(600),
                Textarea::make($bodyKey)->label('Page content')->rows(16)->maxLength(15000)->helperText('Plain text is supported; line breaks are preserved.')->columnSpanFull(),
            ])->columns(2)->visible($visible);
        };

        return [
            $section('Privacy Policy', self::PAGES['privacy-policy'], 'Public privacy information. Only users with settings.manage can edit this page.', fn () => auth()->user()?->can('settings.manage') ?? false),
            $section('Safeguarding', self::PAGES['safeguarding'], 'Safeguarding information and reporting guidance. Access is limited to users with safeguarding.update.', fn () => auth()->user()?->can('safeguarding.update') ?? false),
            $section('Terms', self::PAGES['terms'], 'Public website terms. Only users with settings.manage can edit this page.', fn () => auth()->user()?->can('settings.manage') ?? false),
        ];
    }

    protected function persistState(array $state): void
    {
        $allowed = array_intersect_key($state, array_flip($this->editableKeys()));
        DB::transaction(function () use ($allowed): void {
            $record = SiteSetting::where('key', 'global')->lockForUpdate()->firstOrFail();
            $record->update(['data' => array_replace($record->data, $allowed)]);

            foreach (self::PAGES as $path => [$titleKey, $descriptionKey, $bodyKey]) {
                $keys = array_intersect_key($allowed, array_flip([$titleKey, $descriptionKey, $bodyKey]));
                if ($keys === []) continue;

                $page = ContentPage::where('path', $path)->first();
                if (! $page) continue;
                $page->title = array_replace($page->title ?? [], ['en' => $allowed[$titleKey] ?? $page->translated('title')]);
                $page->description = array_replace($page->description ?? [], ['en' => $allowed[$descriptionKey] ?? $page->translated('description')]);
                $page->body = array_replace($page->body ?? [], ['en' => $allowed[$bodyKey] ?? $page->translated('body')]);
                $page->is_published = true;
                $page->save();
            }
        });

        Notification::make()->title('Policy page saved')->body('The public page now reflects your changes.')->success()->send();
    }
}
