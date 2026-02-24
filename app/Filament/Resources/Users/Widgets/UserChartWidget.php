<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget as BaseChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class UserChartWidget extends BaseChartWidget
{
    use InteractsWithPageFilters;

    public ?User $record;

    public ?array $types = null;

    protected const MAX_POINTS = 10;

    protected ?string $heading = '';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '350px';

    protected function getSourceQuery(string $type): Builder
    {
        $userId = $this->record?->getKey();

        return match ($type) {
            'posts' => Post::query()
                ->whereHas('authors', fn (Builder $query) => $query->whereKey($userId)),
            'comments' => Comment::query()
                ->where('user_id', $userId),
            default => throw new InvalidArgumentException("Unsupported source type [{$type}]"),
        };
    }

    protected function getChartData(string $type): array
    {
        $filters = $this->pageFilters ?? [];
        $startFilter = data_get($filters, 'startDate');
        $endFilter = data_get($filters, 'endDate');

        $start = filled($startFilter) ? Carbon::parse($startFilter) : now()->startOfYear();
        $end = filled($endFilter) ? Carbon::parse($endFilter) : now()->endOfYear();

        $rangeInDays = $start->diffInDays($end);

        $trend = Trend::query(clone $this->getSourceQuery($type))->between(start: $start, end: $end);

        if ($rangeInDays <= 62) {
            $trend = $trend->perDay();
            $labelFormat = 'M j';
        } elseif ($rangeInDays <= 730) {
            $trend = $trend->perMonth();
            $labelFormat = 'M Y';
        } else {
            $trend = $trend->perYear();
            $labelFormat = 'Y';
        }

        $points = $trend->count();

        $runningTotal = (clone $this->getSourceQuery($type))
            ->where('created_at', '<', $start)
            ->count();

        $data = $points->map(function ($point) use (&$runningTotal) {
            $runningTotal += $point->aggregate;

            return $runningTotal;
        })->toArray();

        $labels = $points
            ->map(fn ($point) => Carbon::parse($point->date)->format($labelFormat))
            ->toArray();

        if (count($labels) > self::MAX_POINTS) {
            ['labels' => $labels, 'data' => $data] = $this->downSampleSeries($labels, $data, self::MAX_POINTS);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    protected function downSampleSeries(array $labels, array $data, int $maxPoints): array
    {
        $count = count($labels);

        if ($count <= $maxPoints || $maxPoints < 2) {
            return ['labels' => $labels, 'data' => $data];
        }

        $indices = [];
        for ($i = 0; $i < $maxPoints; $i++) {
            $indices[] = (int) round(($i * ($count - 1)) / ($maxPoints - 1));
        }

        $indices = array_values(array_unique($indices));

        return [
            'labels' => array_values(array_map(fn ($index) => $labels[$index], $indices)),
            'data' => array_values(array_map(fn ($index) => $data[$index], $indices)),
        ];
    }

    protected function getData(): array
    {
        if (! $this->record) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $filterType = data_get($this->pageFilters ?? [], 'filterType');

        $series = [
            'posts' => [
                'label' => 'Blog posts created',
                'color' => '#3b82f6',
            ],
            'comments' => [
                'label' => 'Comments written',
                'color' => '#22c55e',
            ],
        ];

        $types = in_array($filterType, array_keys($series), true)
            ? [$filterType]
            : array_keys($series);

        if (is_array($this->types) && $this->types !== []) {
            $types = array_values(array_intersect($types, $this->types));
        }

        if (! Gate::forUser($this->record)->allows('create', Post::class)) {
            $types = array_values(array_filter($types, fn (string $type): bool => $type !== 'posts'));
        }

        $datasets = [];
        $labels = [];

        foreach ($types as $type) {
            $chartData = $this->getChartData($type);

            if ($labels === []) {
                $labels = $chartData['labels'];
            }

            $color = $series[$type]['color'];

            $datasets[] = [
                'label' => $series[$type]['label'],
                'data' => $chartData['data'],
                'borderColor' => $color,
                'backgroundColor' => "{$color}33",
                'pointBackgroundColor' => $color,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
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
