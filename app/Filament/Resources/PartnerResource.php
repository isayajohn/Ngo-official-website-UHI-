<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Models\Partner;
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

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Content';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Partners';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Partner profile')->schema([
                TextInput::make('name')->required()->maxLength(180),
                TextInput::make('slug')->unique(ignoreRecord: true)->maxLength(255)->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])->helperText('Leave blank on creation to generate a public URL.'),
                TextInput::make('partner_type')->label('Partner type')->maxLength(120)->placeholder('Research, funding, technical, community…'),
                Textarea::make('short_description')->rows(3)->maxLength(500),
                Textarea::make('description')->rows(8)->maxLength(10000)->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
            Section::make('Contact details')->schema([
                TextInput::make('website')->url()->maxLength(255),
                TextInput::make('email')->email()->maxLength(255),
            ])->columns(2)->columnSpanFull(),
            Section::make('Brand images')->schema([
                CmsFields::image('logo'),
                TextInput::make('logo_alt')->label('Logo alternative text')->maxLength(255)->requiredWith('logo'),
                CmsFields::image('featured_image'),
                TextInput::make('image_alt')->label('Featured image alternative text')->maxLength(255)->requiredWith('featured_image'),
            ])->columns(2)->columnSpanFull(),
            Section::make('Website visibility')->schema([
                Toggle::make('active')->default(true)->label('Active'),
                Toggle::make('show_on_website')->default(true)->label('Show on website'),
                Toggle::make('is_featured')->label('Feature on homepage'),
                TextInput::make('display_order')->numeric()->integer()->minValue(0)->default(0)->required(),
            ])->columns(2)->columnSpanFull(),
            Section::make('Search engines')->schema([
                TextInput::make('meta_title')->maxLength(70),
                Textarea::make('meta_description')->maxLength(180),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(), TextColumn::make('partner_type')->label('Type')->placeholder('—'),
            IconColumn::make('active')->boolean(), IconColumn::make('show_on_website')->boolean()->label('Website'), IconColumn::make('is_featured')->boolean()->label('Featured'),
            TextColumn::make('display_order')->sortable(), TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->defaultSort('display_order')->filters([TernaryFilter::make('active'), TernaryFilter::make('show_on_website')->label('Website visible'), TrashedFilter::make()])
            ->recordActions([ViewAction::make(), EditAction::make()])->headerActions([CmsCreateAction::make(static::class, true)]);
    }

    public static function getEloquentQuery(): Builder { return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]); }
    public static function getPages(): array { return ['index' => Pages\ListPartners::route('/'), 'create' => Pages\CreatePartner::route('/create'), 'view' => Pages\ViewPartner::route('/{record}'), 'edit' => Pages\EditPartner::route('/{record}/edit')]; }
}
