<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\PostResource;
use App\Filament\Widgets\ChartWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $user = auth()->user();

        if ($user?->isEditor()) {
            return [
                'All' => Tab::make('All'),
                'Published' => Tab::make('Published')->modifyQueryUsing(function ($query) {
                    $query->where('published', true);
                }),
                'unpublished' => Tab::make('Unpublished')->modifyQueryUsing(function ($query) {
                    $query->where('published', false);
                }),
            ];
        }
        else {
            return [];
        }
    }

    protected function getFooterWidgets(): array
    {
        return [
            ChartWidget::make([
                'types' => ['posts'],
            ]),
        ];
    }
}
