<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentPageResource\Pages;
use App\Models\ContentPage;
use App\Support\CmsCreateAction;
use App\Support\CmsFields;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContentPageResource extends Resource
{
    protected static ?string $model = ContentPage::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Content';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Content Pages';
    protected static ?string $recordTitleAttribute = 'path';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Page identity')->schema([
                TextInput::make('path')->required()->unique(ignoreRecord: true)->maxLength(255)->helperText('Use the public path without a leading slash, for example get-involved/volunteer.'),
                TextInput::make('eyebrow')->maxLength(120)->default('USTAWI GLOBAL HEALTH INITIATIVE'),
                TextInput::make('display_order')->numeric()->integer()->minValue(0)->default(0)->required(),
            ])->columns(2)->columnSpanFull(),
            Section::make('English content')->schema([
                TextInput::make('title.en')->label('Title')->required()->maxLength(255),
                Textarea::make('description.en')->label('Introductory description')->rows(3)->maxLength(600),
                Textarea::make('body.en')->label('Page body')->rows(12)->maxLength(15000)->helperText('Plain text is supported; line breaks are preserved on the public page.')->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
            Section::make('Kiswahili content')->schema([
                TextInput::make('title.sw')->label('Title · Kiswahili')->maxLength(255),
                Textarea::make('description.sw')->label('Introductory description · Kiswahili')->rows(3)->maxLength(600),
                Textarea::make('body.sw')->label('Page body · Kiswahili')->rows(12)->maxLength(15000)->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
            Section::make('Images')->schema([
                CmsFields::image('hero_image'),
                TextInput::make('hero_image_alt')->label('Hero image alternative text')->maxLength(255)->requiredWith('hero_image'),
                CmsFields::image('og_image')->label('Social sharing image'),
            ])->columns(2)->columnSpanFull(),
            Section::make('Search and publishing')->schema([
                TextInput::make('meta_title')->maxLength(70),
                Textarea::make('meta_description')->maxLength(180),
                Toggle::make('is_published')->label('Published on the public website')->default(true),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('path')->label('Page')->searchable()->sortable(),
            TextColumn::make('title.en')->label('English title')->searchable()->wrap(),
            IconColumn::make('is_published')->boolean()->label('Published'),
            TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->defaultSort('display_order')->filters([TernaryFilter::make('is_published'), TrashedFilter::make()])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->headerActions([CmsCreateAction::make(static::class, true)]);
    }

    public static function getEloquentQuery(): Builder { return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]); }
    public static function getPages(): array { return ['index' => Pages\ListContentPages::route('/'), 'create' => Pages\CreateContentPage::route('/create'), 'view' => Pages\ViewContentPage::route('/{record}'), 'edit' => Pages\EditContentPage::route('/{record}/edit')]; }
}
