<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Support\CmsFields;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class Homepage extends BaseSettings
{
    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected function tabKeys(): array
    {
        return [
            'Hero' => ['hero_eyebrow', 'hero_title', 'hero_description', 'hero_primary_label', 'hero_primary_url', 'hero_secondary_label', 'hero_secondary_url', 'hero_image', 'hero_image_alt', 'hero_image_caption', 'hero_image_2', 'hero_image_2_alt', 'hero_image_2_caption', 'hero_image_3', 'hero_image_3_alt', 'hero_image_3_caption'],
            'About UGHI' => ['about_eyebrow', 'about_title', 'about_description', 'about_image', 'about_image_alt', 'about_values'],
            'Our Team' => ['team_eyebrow', 'team_title', 'team_description'],
            'Our History' => ['history_eyebrow', 'history_title', 'history_description', 'history_timeline'],
            'Programme introduction' => ['programmes_eyebrow', 'programmes_title', 'programmes_description'],
            'Featured project' => ['featured_project_id'],
            'Where we work' => ['location_title', 'location_description'],
            'Get involved' => ['cta_title', 'cta_description'],
        ];
    }

    protected function tabIcons(): array
    {
        return [
            'Hero' => 'heroicon-o-sparkles',
            'About UGHI' => 'heroicon-o-information-circle',
            'Our Team' => 'heroicon-o-user-group',
            'Our History' => 'heroicon-o-clock',
            'Programme introduction' => 'heroicon-o-heart',
            'Featured project' => 'heroicon-o-star',
            'Where we work' => 'heroicon-o-map',
            'Get involved' => 'heroicon-o-hand-raised',
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('homepage.manage') ?? false;
    }

    protected function editableKeys(): array
    {
        return ['featured_project_id', 'hero_eyebrow', 'hero_title', 'hero_description', 'hero_primary_label', 'hero_primary_url', 'hero_secondary_label', 'hero_secondary_url', 'hero_image', 'hero_image_alt', 'hero_image_caption', 'hero_image_2', 'hero_image_2_alt', 'hero_image_2_caption', 'hero_image_3', 'hero_image_3_alt', 'hero_image_3_caption', 'about_eyebrow', 'about_title', 'about_description', 'about_image', 'about_image_alt', 'about_values', 'team_eyebrow', 'team_title', 'team_description', 'history_eyebrow', 'history_title', 'history_description', 'history_timeline', 'programmes_eyebrow', 'programmes_title', 'programmes_description', 'location_title', 'location_description', 'cta_title', 'cta_description'];
    }

    protected function fields(): array
    {
        $text = fn (string $key) => TextInput::make($key)->required()->maxLength(255);
        $body = fn (string $key) => Textarea::make($key)->required()->maxLength(1500)->rows(3);
        $url = fn (string $key) => TextInput::make($key)->required()->maxLength(500)->rules(['regex:~^(?:/(?!/)[^\\\\]*|https?://[^\\\\]+)$~'])->helperText('Use a local path starting with / or a full https:// URL.');

        return [
            Section::make('Hero')->description('Your first introduction to UGHI.')->schema([
                $text('hero_eyebrow'), $body('hero_title')->helperText('Use a new line to control desktop line breaks.'), $body('hero_description'),
                $text('hero_primary_label'), $url('hero_primary_url'), $text('hero_secondary_label'), $url('hero_secondary_url'),
                CmsFields::image('hero_image')->label('Hero slide 1 image'), $text('hero_image_alt')->label('Slide 1 alternative text'), $text('hero_image_caption')->label('Slide 1 caption'),
                CmsFields::image('hero_image_2')->label('Hero slide 2 image'), $text('hero_image_2_alt')->label('Slide 2 alternative text')->default('Community outreach for healthier futures'), $text('hero_image_2_caption')->label('Slide 2 caption')->default('Community outreach for healthier futures'),
                CmsFields::image('hero_image_3')->label('Hero slide 3 image'), $text('hero_image_3_alt')->label('Slide 3 alternative text')->default('Health education for lasting change'), $text('hero_image_3_caption')->label('Slide 3 caption')->default('Health education for lasting change'),
            ])->columns(2),
            Section::make('About UGHI')->description('This content also appears on the About UGHI page.')->schema([
                $text('about_eyebrow'), $text('about_title'), $body('about_description'), CmsFields::image('about_image'), $text('about_image_alt'),
                Repeater::make('about_values')->label('Core values')->schema([TextInput::make('label')->required()->maxLength(80)])->defaultItems(0)->maxItems(12)->reorderable()->collapsible()->columnSpanFull(),
            ]),
            Section::make('Our Team')->description('Manage the introduction above the public Board and Senior Management profiles. Manage the people and reporting hierarchy under Organization → Staff & Leadership.')->schema([
                $text('team_eyebrow'), $text('team_title'), $body('team_description'),
            ]),
            Section::make('Our History')->description('Manage the public history introduction and timeline. Use verified milestones only.')->schema([
                $text('history_eyebrow'), $text('history_title'), $body('history_description'),
                Repeater::make('history_timeline')->label('Timeline milestones')->schema([
                    TextInput::make('year')->required()->maxLength(40)->helperText('A year or period, for example 2018–2020.'),
                    TextInput::make('title')->required()->maxLength(160),
                    Textarea::make('description')->required()->rows(3)->maxLength(1200)->columnSpanFull(),
                ])->columns(2)->defaultItems(0)->maxItems(40)->reorderable()->collapsible()->columnSpanFull(),
            ]),
            Section::make('Programme introduction')->schema([$text('programmes_eyebrow'), $text('programmes_title'), $body('programmes_description')]),
            Section::make('Featured project')->description('Select a published project, or leave blank to use the latest project marked featured.')->schema([
                Select::make('featured_project_id')->label('Homepage featured project')->searchable()
                    ->getSearchResultsUsing(fn (string $search) => Project::published()->where('title->en', 'like', '%'.$search.'%')->limit(50)->get()->mapWithKeys(fn ($p) => [$p->id => $p->translated('title')]))
                    ->getOptionLabelUsing(fn ($value) => Project::find($value)?->translated('title')),
            ]),
            Section::make('Where we work')->schema([$text('location_title'), $body('location_description')]),
            Section::make('Get involved')->schema([$text('cta_title'), $body('cta_description')]),
        ];
    }
}
