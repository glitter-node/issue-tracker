<?php

namespace App\Livewire;

use App\Models\Issue;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Livewire\WithPagination;

class IssueList extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    #[Reactive]
    public ?int $selectedIssueId = null;

    #[Reactive]
    public string $searchQuery = '';

    #[Reactive]
    public string $viewScope = 'all';

    #[Reactive]
    public string $statusFilter = '';

    public function selectIssue(int $issueId): void
    {
        $this->selectedIssueId = $issueId;
        $this->dispatch('workspace-select-issue', issueId: $issueId);
    }

    public function startCreate(): void
    {
        $this->dispatch('workspace-start-create');
    }

    #[On('workspace-issue-saved')]
    #[On('workspace-issue-deleted')]
    public function refreshList(): void
    {
    }

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
    }

    public function updatedViewScope(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $userId = auth()->id();

        $issues = Issue::query()
            ->with([
                'creator:id,name',
                'assignee:id,name',
            ])
            ->withCount('comments')
            ->when($this->searchQuery !== '', function (Builder $query): void {
                $query->where(function (Builder $search): void {
                    $search
                        ->where('title', 'like', '%'.$this->searchQuery.'%')
                        ->orWhere('description', 'like', '%'.$this->searchQuery.'%');
                });
            })
            ->when($this->viewScope === 'mine' && $userId !== null, fn (Builder $query) => $query->where('assigned_to', $userId))
            ->when($this->viewScope === 'created' && $userId !== null, fn (Builder $query) => $query->where('created_by', $userId))
            ->when($this->statusFilter !== '', fn (Builder $query) => $query->where('status', $this->statusFilter))
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('livewire.issue-list', [
            'issues' => $issues,
        ]);
    }
}
