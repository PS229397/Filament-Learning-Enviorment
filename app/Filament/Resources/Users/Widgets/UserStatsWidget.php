<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class UserStatsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    public ?User $record;

    public ?array $types = null;

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

    protected function getDateRange(): array
    {
        $filters = $this->pageFilters ?? [];
        $startFilter = data_get($filters, 'startDate');
        $endFilter = data_get($filters, 'endDate');

        $start = filled($startFilter) ? Carbon::parse($startFilter) : now()->startOfYear();
        $end = filled($endFilter) ? Carbon::parse($endFilter) : now()->endOfYear();

        return [$start, $end];
    }

    protected function getSourceCount(string $type): int
    {
        $filters = $this->pageFilters ?? [];
        $startFilter = data_get($filters, 'startDate');
        $endFilter = data_get($filters, 'endDate');

        return $this->getSourceQuery($type)
            ->when(filled($startFilter), fn ($query) => $query->where('created_at', '>=', Carbon::parse($startFilter)))
            ->when(filled($endFilter), fn ($query) => $query->where('created_at', '<=', Carbon::parse($endFilter)))
            ->count();
    }

    protected function getSourceTrendData(string $type): array
    {
        [$start, $end] = $this->getDateRange();
        $sourceQuery = $this->getSourceQuery($type);

        $trend = Trend::query(clone $sourceQuery)
            ->between(start: $start, end: $end)
            ->perDay()
            ->count();

        $runningTotal = (clone $sourceQuery)
            ->where('created_at', '<', $start)
            ->count();

        return $trend->map(function ($point) use (&$runningTotal) {
            $runningTotal += $point->aggregate;

            return $runningTotal;
        })->toArray();
    }

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $available = [
            'posts' => [
                'title' => 'Posts',
                'description' => 'Posts created by this user',
                'icon' => 'heroicon-s-list-bullet',
                'color' => 'success',
            ],
            'comments' => [
                'title' => 'Comments',
                'description' => 'Comments written by this user',
                'icon' => 'heroicon-s-chat-bubble-left-right',
                'color' => 'success',
            ],
        ];

        $types = $this->types ?? array_keys($available);
        $canCreatePosts = Gate::forUser($this->record)->allows('create', Post::class);

        return collect($types)
            ->filter(fn (string $type): bool => array_key_exists($type, $available))
            ->reject(fn (string $type): bool => $type === 'posts' && ! $canCreatePosts)
            ->map(function (string $type) use ($available): Stat {
                return Stat::make($available[$type]['title'], $this->getSourceCount($type))
                    ->label('')
                    ->description($available[$type]['description'])
                    ->descriptionIcon($available[$type]['icon'], IconPosition::After)
                    ->chart($this->getSourceTrendData($type))
                    ->color($available[$type]['color']);
            })
            ->values()
            ->all();
    }
}
