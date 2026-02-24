<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Widgets\ChartWidget;
use App\Filament\Widgets\StatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            ChartWidget::make([
                'types' => ['users'],
            ]),
        ];
    }
}
