<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    protected static ?string $relatedResource = PostResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Create a Post')
                    ->schema([
                        TextInput::make('title')
                            ->required(),
                        TextInput::make('slug')
                            ->required(),
                        ColorPicker::make('color')
                            ->required(),
                        MarkdownEditor::make('content')
                            ->default(null)
                            ->columnSpanFull(),
                    ])->columnSpan(3)->columns(2),

                Group::make()->schema([
                    Section::make('Image')
                        ->collapsible()
                        ->schema([
                            FileUpload::make('thumbnail')
                                ->disk('public')
                                ->columnSpanFull(),
                        ]),
                    Section::make('Meta')->schema([
                        TagsInput::make('tags'),
                        Toggle::make('published')
                            ->default(false),
                    ]),
                ])->columnSpan(1),
            ])->columns(4);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('tags')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('published')
                    ->boolean()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->modal()
                    ->modalHeading(__('Post'))
                    ->modalWidth(Width::FitContent),
                DeleteAction::make()
                ->hidden(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                ->hidden(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modal()
                    ->modalHeading(__('Post'))
                    ->modalWidth(Width::FitContent),
            ]);
    }
}
