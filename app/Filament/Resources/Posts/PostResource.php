<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Pages\ViewPost;
use App\Filament\Resources\Posts\RelationManagers\AuthorsRelationManager;
use App\Filament\Resources\Posts\RelationManagers\CommentsRelationManager;
use App\Models\Category;
use App\Models\Post;
use BackedEnum;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Create a post')->tabs([
                    Tab::make('Title')
                        ->icon('heroicon-s-pencil-square')
                        ->iconPosition(IconPosition::After)
                        ->schema([
                        TextInput::make('title')
                            ->required(),
                        TextInput::make('slug')
                            ->required(),
                        ColorPicker::make('color')
                            ->required(),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2)->columnSpanFull(),
                    Tab::make('Content')
                        ->icon('heroicon-s-document-text')
                        ->iconPosition(IconPosition::After)
                        ->schema([
                        MarkdownEditor::make('content')
                            ->default(null)
                            ->columnSpanFull(),
                    ]),
                    Tab::make('Metadata')
                        ->icon('heroicon-s-photo')
                        ->iconPosition(IconPosition::After)
                        ->schema([
                            FileUpload::make('thumbnail')
                                ->disk('public')
                                ->columnSpanFull(),
                            TagsInput::make('tags'),
                            Toggle::make('published')
                                ->default(false),
                        ]),
                ])->columnSpanFull()->persistTabInQueryString(),
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->disk('public')
                    ->imageHeight(50)
                    ->imageWidth(50),
                ColorColumn::make('color')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('tags')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('main_author')
                    ->label('Author')
                    ->state(function (Post $record): string {
                        return $record->authors
                            ->firstWhere('pivot.order', 1)?->name ?? '-';
                    }),
                IconColumn::make('published')
                    ->boolean()
                    ->hidden(fn () => ! auth()->user()?->isEditor()),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
                DeleteAction::make()
                ->label(''),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Post')
                    ->schema([
                        TextEntry::make('title')
                            ->hiddenLabel(),
                        TextEntry::make('content')
                            ->hiddenLabel()
                            ->prose(),
                    ])
                    ->columnSpan(4)
                    ->extraAttributes([
                        'class' => 'min-h-full',
                    ]),
                Section::make('')
                    ->schema([
                        ImageEntry::make('thumbnail')
                            ->disk('public')
                            ->hiddenLabel()
                            ->height('100%')
                            ->width('100%'),
                        Group::make([
                            TextEntry::make('category.name')
                                ->label('Category'),
                            TextEntry::make('tags')
                                ->formatStateUsing(function ($state): string {
                                    if (is_array($state)) {
                                        return implode(', ', $state);
                                    }

                                    if (blank($state)) {
                                        return '-';
                                    }

                                    return (string) $state;
                                }),
                        ]),
                    ])
            ])
            ->columns(5);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
            AuthorsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'view' => ViewPost::route('/{record}'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
