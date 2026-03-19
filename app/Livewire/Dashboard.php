<?php

namespace App\Livewire;

use App\Models\Issue;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $statusCounts = $this->countsByColumn('status', Issue::STATUSES);
        $priorityCounts = $this->countsByColumn('priority', Issue::PRIORITIES);
        $categoryCounts = $this->countsByColumn('category', Issue::CATEGORIES);

        return view('livewire.dashboard', [
            'totalIssues' => Issue::query()->count(),
            'statusCounts' => $statusCounts,
            'priorityCounts' => $priorityCounts,
            'categoryCounts' => $categoryCounts,
            'latestIssues' => Issue::query()
                ->with(['creator:id,name', 'assignee:id,name'])
                ->latest()
                ->limit(5)
                ->get(),
            'statusWidths' => $this->widthClasses($statusCounts, max(1, ...array_values($statusCounts))),
            'priorityWidths' => $this->widthClasses($priorityCounts, max(1, ...array_values($priorityCounts))),
            'categoryWidths' => $this->widthClasses($categoryCounts, max(1, ...array_values($categoryCounts))),
        ])->layout('components.layouts.app')
            ->title('Dashboard');
    }

    /**
     * @param  list<string>  $keys
     * @return array<string, int>
     */
    private function countsByColumn(string $column, array $keys): array
    {
        $counts = Issue::query()
            ->selectRaw("{$column}, COUNT(*) as aggregate")
            ->whereIn($column, $keys)
            ->groupBy($column)
            ->pluck('aggregate', $column)
            ->map(fn ($count) => (int) $count)
            ->all();

        return collect($keys)
            ->mapWithKeys(fn ($key) => [$key => $counts[$key] ?? 0])
            ->all();
    }

    private function widthClasses(array $counts, int $max): array
    {
        return collect($counts)
            ->mapWithKeys(function (int $count, string $key) use ($max): array {
                $ratio = $max > 0 ? (int) round(($count / $max) * 100) : 0;
                $step = (int) (round($ratio / 5) * 5);

                return [$key => 'metric-bar-'.$step];
            })
            ->all();
    }
}
