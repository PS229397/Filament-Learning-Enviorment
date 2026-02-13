<?php

namespace App\Filament\Resources\Posts\RelationManagers;

use App\Models\Comment;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static bool $shouldSkipAuthorization = true;

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('comment')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comment')
            ->columns([
                TextColumn::make('user.name'),
                TextColumn::make('comment'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (Comment $record): bool => $record->user_id === auth()->id())
                    ->authorize(fn (Comment $record): bool => $record->user_id === auth()->id()),
                DeleteAction::make()
                    ->visible(fn (Comment $record): bool => $record->user_id === auth()->id() || (auth()->user()?->isAdmin() ?? false))
                    ->authorize(fn (Comment $record): bool => $record->user_id === auth()->id() || (auth()->user()?->isAdmin() ?? false)),
            ]);
    }
}
