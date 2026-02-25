<?php

namespace App\Filament\Resources;

use App\Filament\Exports\UserExporter;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Widgets\UserChartWidget;
use App\Filament\Resources\Users\Widgets\UserStatsWidget;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|null|UnitEnum $navigationGroup = 'Users';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                ->required()
                ->maxLength(255),
                TextInput::make('email')
                ->required()
                ->maxLength(255)
                ->email(),
                Select::make('role')
                ->required()
                ->options(User::roleOptions())
                ->default(User::ROLE_EDITOR),
                TextInput::make('password')
                ->required()
                ->maxLength(255)
                ->password()
                ->visibleOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(function (string $state):string {
                        return match ($state) {
                            'admin'=>'danger', //red
                            'editor'=>'info', //blue
                            'user'=>'success', //green
                        };
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('D, d M, Y')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View user')
                    ->icon('heroicon-o-eye'),
                DeleteAction::make()
                    ->label('Delete user')
                    ->icon('heroicon-o-trash'),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->exporter(UserExporter::class)
                    ->formats([
                        ExportFormat::Csv
                    ]),
                ExportBulkAction::make()
                    ->label('Export selected users')
                    ->exporter(UserExporter::class)
                    ->formats([
                        ExportFormat::Csv
                    ]),
                DeleteBulkAction::make()
                    ->label('Delete selected users'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User details')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email'),
                        TextEntry::make('role')
                            ->badge(),
                        TextEntry::make('created_at')
                            ->dateTime('D, d M, Y'),
                    ])
                    ->columns(2)
                    ->columnSpan(2),
                Livewire::make(UserStatsWidget::class)
                    ->columnSpan(1),
                Livewire::make(UserChartWidget::class)
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
