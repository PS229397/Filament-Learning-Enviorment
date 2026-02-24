<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;

class StatsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    public ?array $types = null;

    protected static ?int $sort = 1;

    protected function getSource(string $type): string
    {
        return match ($type) {
            'users' => User::class,
            'posts' => Post::class,
            'categories' => Category::class,
        };
    }

    protected function getStatCount(string $type): int
    {
        $modelClass = $this->getSource($type);

        $filters = $this->pageFilters ?? [];
        $startFilter = data_get($filters, 'startDate');
        $endFilter = data_get($filters, 'endDate');

        return $modelClass::query()
            ->when(filled($startFilter), fn ($query) => $query->where('created_at', '>=', Carbon::parse($startFilter)))
            ->when(filled($endFilter), fn ($query) => $query->where('created_at', '<=', Carbon::parse($endFilter)))
            ->count();
    }

    protected function getStatData(string $type): array
    {
        $modelClass = $this->getSource($type);
        $filters = $this->pageFilters ?? [];

        $startFilter = data_get($filters, 'startDate');
        $endFilter = data_get($filters, 'endDate');

        $start = filled($startFilter) ? Carbon::parse($startFilter) : now()->startOfYear();
        $end = filled($endFilter) ? Carbon::parse($endFilter) : now()->endOfYear();

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
        $available = [
            'users' => [
                'title' => 'Users',
                'description' => 'Users that have joined!',
                'icon' => 'heroicon-s-user-group',
                'color' => 'success',
            ],
            'posts' => [
                'title' => 'Posts',
                'description' => 'Posts have been created!',
                'icon' => 'heroicon-s-list-bullet',
                'color' => 'success',
            ],
            'categories' => [
                'title' => 'Categories',
                'description' => 'Categories have been created!',
                'icon' => 'heroicon-s-swatch',
                'color' => 'success',
            ],
        ];

        $types = $this->types ?? array_keys($available);

        return collect($types)
            ->filter(fn (string $type): bool => array_key_exists($type, $available))
            ->map(function (string $type) use ($available): Stat {
                return Stat::make($available[$type]['title'], $this->getStatCount($type))
                    ->label('')
                    ->description($available[$type]['description'])
                    ->descriptionIcon($available[$type]['icon'], IconPosition::After)
                    ->chart($this->getStatData($type))
                    ->color($available[$type]['color']);
            })
            ->values()
            ->all();
    }
}
