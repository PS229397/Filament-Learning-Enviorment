<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\CategoryResource;
use App\Filament\Widgets\ChartWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

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
                'types' => ['categories'],
            ]),
        ];
    }
}
