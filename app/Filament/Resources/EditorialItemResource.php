<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EditorialItemResource\Pages;
use App\Models\EditorialItem;
use App\Support\CmsFields;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;

class EditorialItemResource extends Resource
{
    protected static ?string $model = EditorialItem::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Content';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationLabel = 'News, Events & Media';
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        $canPublish = fn (): bool => auth()->user()?->can('news.publish') ?? false;

        return $schema->components([
            Section::make('Content')->schema([
                Select::make('type')->label('Section')->options(fn (string $operation): array => collect(EditorialItem::TYPES)
                    ->filter(fn ($label, $type) => auth()->user()?->can($type.($operation === 'create' ? '.create' : '.update')) ?? false)
                    ->all())->required()->live()->disabledOn('edit'),
                TextInput::make('title')->required()->maxLength(220),
                TextInput::make('slug')->unique(ignoreRecord: true)->maxLength(255)->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])->helperText('Leave blank to generate a URL from the title.'),
                Textarea::make('summary')->label(fn (Get $get) => $get('type') === 'gallery' ? 'Caption' : 'Short description')->rows(3)->maxLength(1000)->columnSpanFull(),
                Textarea::make('body')->label('Full content')->rows(12)->maxLength(30000)->visible(fn (Get $get) => $get('type') !== 'gallery')->columnSpanFull(),
                CmsFields::image('image')->label('Image')->required(fn (Get $get, string $operation) => $get('type') === 'gallery' && $operation === 'create'),
                TextInput::make('image_alt')->label('Image alternative text')->maxLength(255)->requiredWith('image'),
            ])->columns(2)->columnSpanFull(),
            Section::make('Event details')->schema([
                DateTimePicker::make('event_starts_at')->label('Starts at')->required(fn (Get $get) => $get('type') === 'event')->visible(fn (Get $get) => $get('type') === 'event'),
                DateTimePicker::make('event_ends_at')->label('Ends at')->visible(fn (Get $get) => $get('type') === 'event')->rules(fn (Get $get) => filled($get('event_starts_at')) ? ['after_or_equal:'.$get('event_starts_at')] : []),
                TextInput::make('event_location')->maxLength(255)->visible(fn (Get $get) => $get('type') === 'event'),
            ])->columns(2)->visible(fn (Get $get) => $get('type') === 'event')->columnSpanFull(),
            Section::make('Publication file or link')->description('Upload an accessible PDF or link to the original publication.')->schema([
                FileUpload::make('document')->label('PDF file')->disk('public')->directory('publications')->acceptedFileTypes(['application/pdf'])->maxSize(20480)->downloadable()
                    ->placeholder('Click the button below to upload<br><span class="filepond--label-action">Browse file</span>')->helperText('PDF only. Maximum file size: 20 MB.')
                    ->visible(fn (Get $get) => $get('type') === 'publication'),
                TextInput::make('document_label')->label('Link label')->maxLength(120)->visible(fn (Get $get) => $get('type') === 'publication'),
                TextInput::make('external_url')->label('External URL')->url()->maxLength(2048)->visible(fn (Get $get) => $get('type') === 'publication'),
            ])->columns(2)->visible(fn (Get $get) => $get('type') === 'publication')->columnSpanFull(),
            Section::make('Publishing')->description('Only users with the news.publish permission can publish or schedule content.')->schema([
                Toggle::make('is_published')->label('Published')->default(false)->visible($canPublish)->dehydrated($canPublish),
                DateTimePicker::make('published_at')->label('Publish at')->visible($canPublish)->dehydrated($canPublish)->helperText('Leave blank to publish immediately. A future date schedules the item.'),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('type')->formatStateUsing(fn ($state) => EditorialItem::TYPES[$state] ?? $state)->badge()->sortable(),
            TextColumn::make('title')->searchable()->sortable()->wrap(),
            TextColumn::make('published_at')->dateTime()->sortable()->placeholder('Draft'),
            IconColumn::make('is_published')->boolean()->label('Published'),
            TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->defaultSort('updated_at', 'desc')->filters([
            SelectFilter::make('type')->options(EditorialItem::TYPES),
            SelectFilter::make('is_published')->options(['1' => 'Published / scheduled', '0' => 'Draft']),
            TrashedFilter::make(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ])->headerActions([
            \Filament\Actions\CreateAction::make()->modal()->modalWidth(\Filament\Support\Enums\Width::SevenExtraLarge)
                ->stickyModalHeader()->stickyModalFooter()->closeModalByClickingAway(false),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEditorialItems::route('/'),
            'create' => Pages\CreateEditorialItem::route('/create'),
            'edit' => Pages\EditEditorialItem::route('/{record}/edit'),
        ];
    }
}
