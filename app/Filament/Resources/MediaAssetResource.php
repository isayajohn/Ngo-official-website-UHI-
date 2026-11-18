<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaAssetResource\Pages;
use App\Models\MediaAsset;
use App\Support\CmsCreateAction;
use App\Support\CmsFields;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MediaAssetResource extends Resource
{
    protected static ?string $model = MediaAsset::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Content';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Media Library';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Asset')->schema([
                TextInput::make('name')->required()->maxLength(180),
                CmsFields::image('path')->label('Image file')->required(fn (string $operation) => $operation === 'create'),
                TextInput::make('collection')->default('general')->maxLength(80)->helperText('Use a short collection name such as homepage, partners or campaigns.'),
                Hidden::make('uploaded_by')->default(fn () => auth()->id()),
            ])->columns(2)->columnSpanFull(),
            Section::make('Accessibility and publishing')->schema([
                TextInput::make('alt_text')->label('Alternative text')->maxLength(255)->required(),
                Textarea::make('caption')->rows(3)->maxLength(500),
                Toggle::make('is_public')->label('Publicly usable')->default(true),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('path')->disk('public')->label('Preview'), TextColumn::make('name')->searchable()->sortable(), TextColumn::make('collection')->badge(),
            TextColumn::make('uploader.name')->label('Uploaded by')->placeholder('System'), IconColumn::make('is_public')->boolean()->label('Public'), TextColumn::make('created_at')->dateTime()->sortable(),
        ])->defaultSort('created_at', 'desc')->filters([TernaryFilter::make('is_public'), TrashedFilter::make()])
            ->recordActions([ViewAction::make(), EditAction::make()])->headerActions([CmsCreateAction::make(static::class, true)]);
    }

    public static function getEloquentQuery(): Builder { return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]); }
    public static function getPages(): array { return ['index' => Pages\ListMediaAssets::route('/'), 'create' => Pages\CreateMediaAsset::route('/create'), 'view' => Pages\ViewMediaAsset::route('/{record}'), 'edit' => Pages\EditMediaAsset::route('/{record}/edit')]; }
}
