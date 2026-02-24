<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('')
            ->schema([
                Select::make('filterType')
                    ->options([
                        '' => 'All',
                        'users' => 'Users',
                        'posts' => 'Posts',
                        'categories' => 'Categories',
                    ])->default('')->selectablePlaceholder(false),
                DatePicker::make('startDate')
                    ->columnSpan(2),
                DatePicker::make('endDate')
                    ->columnSpan(2),
                Actions::make([
                    Action::make('resetFilters')
                        ->label('Reset filters')
                        ->color('gray')
                        ->action(function (): void {
                            $this->filters = null;

                            $this->getFiltersForm()->fill([
                                'filterType' => '',
                                'startDate' => null,
                                'endDate' => null,
                            ]);

                            session()->forget($this->getFiltersSessionKey());
                        }),
                ])->alignEnd(),
            ])->columns(6)->columnSpanFull()
        ]);
    }
}
