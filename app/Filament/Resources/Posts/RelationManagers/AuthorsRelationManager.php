<?php

namespace App\Filament\Resources\Posts\RelationManagers;

use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuthorsRelationManager extends RelationManager
{
    protected static string $relationship = 'authors';

    protected static bool $shouldSkipAuthorization = true;

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('name')
                    ->options(
                        User::query()
                            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_EDITOR])
                            ->orderBy('name')
                            ->pluck('name', 'name')
                            ->all()
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('order')
                    ->numeric()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('order')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email')
                    ->label('Email address'),
                TextColumn::make('order'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->recordSelectOptionsQuery(fn ($query) => $query->whereIn('role', [User::ROLE_ADMIN, User::ROLE_EDITOR]))
                    ->schema(fn (AttachAction $action): array =>[
                    $action->getRecordSelect(),
                    TextInput::make('order')->numeric()->required(),
                ])
                    ->preloadRecordSelect()
                    ->visible(fn (): bool => auth()->user()?->isEditor() ?? false),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (): bool => auth()->user()?->isEditor() ?? false),
                DetachAction::make()
                    ->visible(fn (): bool => auth()->user()?->isEditor() ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->isEditor() ?? false),
                ]),
            ]);
    }
}
