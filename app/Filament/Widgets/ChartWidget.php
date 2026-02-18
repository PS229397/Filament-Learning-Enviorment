<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Filament\Widgets\ChartWidget as BaseChartWidget;
use Flowframe\Trend\Trend;

class ChartWidget extends BaseChartWidget
{
    protected ?string $heading = 'Posts';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getSource(string $type): string
    {
        return match ($type) {
            'users' => User::class,
            'posts' => Post::class,
            'categories' => Category::class,
        };
    }

    protected function getChartData(string $type): array
    {
        $modelClass = $this->getSource($type);

        $start = now()->startOfYear();
        $end = now()->endOfYear();

        $trend = Trend::model($modelClass)
            ->between(start: $start, end: $end)
            ->perMonth()
            ->count();

        $runningTotal = $modelClass::query()
            ->where('created_at', '<', $start)
            ->count();

        return $trend->map(function ($point) use (&$runningTotal) {
            $runningTotal += $point->aggregate;
            return $runningTotal;
        })->toArray();
    }

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Users joined',
                    'data' => $this->getChartData('users'),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => '#22c55e33',
                    'pointBackgroundColor' => '#22c55e',
                ],
                [
                    'label' => 'Blog posts created',
                    'data' => $this->getChartData('posts'),
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
