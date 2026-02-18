<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;

class StatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getSource(string $type): string
    {
        return match ($type) {
            'users' => User::class,
            'posts' => Post::class,
            'categories' => Category::class,
        };
    }
    protected function getStatData(string $type): array
    {
        $modelClass = $this->getSource($type);
        $start = now()->subDays(30)->startOfDay();
        $end = now();

        $trend = Trend::model($modelClass)
            ->between(start: $start, end: $end)
            ->perDay()
            ->count();

        $runningTotal = $modelClass::query()
            ->where('created_at', '<', $start)
            ->count();

        return $trend->map(function ($point) use (&$runningTotal) {
            $runningTotal += $point->aggregate;
            return $runningTotal;
        })->toArray();
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Users', User::count())
                ->label('')
                ->description('Users that have joined!')
                ->descriptionIcon('heroicon-s-user-group', IconPosition::After)
                ->chart($this->getStatData('users'))
                ->color('success'),
            Stat::make('Posts', Post::count())
                ->label('')
                ->description('Posts have been created!')
                ->descriptionIcon('heroicon-s-list-bullet', IconPosition::After)
                ->chart($this->getStatData('posts'))
                ->color('success'),
            Stat::make('Categories', Category::count())
                ->label('')
                ->description('Categories have been created!')
                ->descriptionIcon('heroicon-s-swatch', IconPosition::After)
                ->chart($this->getStatData('categories'))
                ->color('success'),
        ];
    }
}
