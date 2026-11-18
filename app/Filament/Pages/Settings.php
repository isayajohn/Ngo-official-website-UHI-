<?php

namespace App\Filament\Pages;

use App\Support\CmsFields;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class Settings extends BaseSettings
{
    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected function tabKeys(): array
    {
        return [
            'Organization' => ['organization_name', 'abbreviation', 'tagline', 'logo', 'logo_alt', 'favicon', 'favicon_alt', 'vision', 'mission', 'goal'],
            'Navigation' => ['header_menu'],
            'Footer' => ['footer_about', 'footer_links', 'footer_legal_links'],
            'Contact' => ['email', 'phone', 'whatsapp', 'physical_address', 'postal_address'],
            'Social media' => ['facebook', 'instagram', 'linkedin', 'youtube', 'x'],
            'Default SEO' => ['default_meta_title', 'default_meta_description', 'default_og_image'],
            'Performance' => ['performance_score_cap', 'performance_on_track_threshold', 'performance_critical_threshold', 'performance_enforce_100_percent_weights'],
            'Maintenance' => ['public_maintenance_mode', 'public_maintenance_message'],
        ];
    }

    protected function tabIcons(): array
    {
        return [
            'Organization' => 'heroicon-o-building-office-2',
            'Navigation' => 'heroicon-o-bars-3',
            'Footer' => 'heroicon-o-bars-3-bottom-left',
            'Contact' => 'heroicon-o-phone',
            'Social media' => 'heroicon-o-share',
            'Default SEO' => 'heroicon-o-magnifying-glass',
            'Performance' => 'heroicon-o-chart-bar',
            'Maintenance' => 'heroicon-o-wrench-screwdriver',
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('settings.manage') ?? false;
    }

    protected function editableKeys(): array
    {
        return ['organization_name', 'abbreviation', 'tagline', 'logo', 'logo_alt', 'favicon', 'favicon_alt', 'mission', 'vision', 'goal', 'header_menu', 'email', 'phone', 'whatsapp', 'physical_address', 'postal_address', 'facebook', 'instagram', 'linkedin', 'youtube', 'x', 'footer_about', 'footer_links', 'footer_legal_links', 'public_maintenance_mode', 'public_maintenance_message', 'default_meta_title', 'default_meta_description', 'default_og_image', 'performance_score_cap', 'performance_on_track_threshold', 'performance_critical_threshold', 'performance_enforce_100_percent_weights'];
    }

    protected function fields(): array
    {
        return [
            Section::make('Organization')->schema([
                TextInput::make('organization_name')->required()->maxLength(255), TextInput::make('abbreviation')->required()->maxLength(20),
                Textarea::make('tagline')->required()->maxLength(500),
                CmsFields::image('logo')->label('Organization logo'), TextInput::make('logo_alt')->label('Logo alternative text')->maxLength(255)->helperText('Used by screen readers and when the logo cannot load.'),
                CmsFields::favicon()->label('Browser favicon (.ico)'), TextInput::make('favicon_alt')->label('Favicon description')->maxLength(255)->helperText('A short description for the browser icon asset.'),
                Textarea::make('vision')->required()->maxLength(2000), Textarea::make('mission')->required()->maxLength(4000), Textarea::make('goal')->required()->maxLength(4000),
            ]),
            Section::make('Header navigation')->description('Edit the public navigation menu and dropdown links. Items with child links open as dropdowns.')->schema([
                Repeater::make('header_menu')->label('Menu items')->schema([
                    TextInput::make('label')->required()->maxLength(40),
                    TextInput::make('url')->label('Link URL')->maxLength(500)->rules(['nullable', 'regex:/^(\/(?!\/)|https?:\/\/)/i'])->helperText('Use a site path such as /about or a full http(s) URL. This is also shown as the first dropdown link when child links are set.'),
                    Repeater::make('children')->label('Dropdown links')->schema([
                        TextInput::make('label')->required()->maxLength(60),
                        TextInput::make('url')->required()->maxLength(500)->rules(['regex:/^(\/(?!\/)|https?:\/\/)/i'])->helperText('Use a site path or full http(s) URL.'),
                    ])->columns(2)->defaultItems(0)->maxItems(20)->collapsible()->reorderable(),
                ])->columns(2)->defaultItems(0)->maxItems(12)->collapsible()->reorderable(),
            ]),
            Section::make('Footer')->description('Manage the footer introduction and the link groups shown on every public page.')->schema([
                Textarea::make('footer_about')->label('Short organization description')->maxLength(600)->rows(3),
                Repeater::make('footer_links')->label('Footer link groups')->schema([
                    TextInput::make('heading')->required()->maxLength(40),
                    Repeater::make('links')->label('Links')->schema([
                        TextInput::make('label')->required()->maxLength(80),
                        TextInput::make('url')->required()->maxLength(500)->rules(['regex:/^(\/(?!\/)|https?:\/\/)/i'])->helperText('Use a site path such as /about or a full http(s) URL.'),
                    ])->columns(2)->defaultItems(1)->maxItems(12)->collapsible(),
                ])->columns(1)->defaultItems(1)->maxItems(6)->collapsible()->reorderable(),
                Repeater::make('footer_legal_links')->label('Policy and legal links')->schema([
                    TextInput::make('label')->required()->maxLength(80),
                    TextInput::make('url')->required()->maxLength(500)->rules(['regex:/^(\/(?!\/)|https?:\/\/)/i'])->helperText('Use a site path such as /privacy-policy or a full http(s) URL.'),
                ])->columns(2)->defaultItems(1)->maxItems(8)->collapsible()->reorderable(),
            ]),
            Section::make('Contact')->description('Leave unknown details blank. Only verified details are shown publicly.')->schema([
                TextInput::make('email')->email()->maxLength(255), TextInput::make('phone')->tel()->maxLength(40), TextInput::make('whatsapp')->tel()->maxLength(40), Textarea::make('physical_address')->maxLength(1000), Textarea::make('postal_address')->maxLength(1000),
            ])->columns(2),
            Section::make('Social media')->schema(array_map(fn ($key) => TextInput::make($key)->url()->maxLength(500)->rules(['starts_with:https://,http://']), ['facebook', 'instagram', 'linkedin', 'youtube', 'x']))->columns(2),
            Section::make('Default SEO')->schema([TextInput::make('default_meta_title')->required()->maxLength(70), Textarea::make('default_meta_description')->required()->maxLength(180), CmsFields::image('default_og_image')]),
            Section::make('Performance settings')->description('Starter thresholds are editable organizational configuration, not fixed policy.')->schema([
                TextInput::make('performance_score_cap')->label('Maximum KPI score')->numeric()->minValue(100)->maxValue(200)->required()->helperText('Caps normalized results so exceptional outliers do not dominate weighted scores.'),
                TextInput::make('performance_on_track_threshold')->label('On track from')->numeric()->minValue(0)->maxValue(200)->required(),
                TextInput::make('performance_critical_threshold')->label('Critical below')->numeric()->minValue(0)->maxValue(200)->required(),
                Toggle::make('performance_enforce_100_percent_weights')->label('Require KPI weights to total 100%'),
            ])->columns(2),
            Section::make('Public website maintenance')->description('Turn this on to temporarily replace public pages with a maintenance message. The admin panel remains available to authorized administrators.')->schema([
                Toggle::make('public_maintenance_mode')->label('Enable maintenance mode')->helperText('Public visitors will receive a maintenance page with a temporary unavailable response.'),
                Textarea::make('public_maintenance_message')->label('Message for visitors')->rows(4)->maxLength(1000)->required(),
            ])->columns(1),
        ];
    }
}
