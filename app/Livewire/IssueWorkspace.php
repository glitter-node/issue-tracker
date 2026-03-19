<?php

namespace App\Livewire;

use App\Models\Issue;
use Livewire\Attributes\On;
use Livewire\Component;

class IssueWorkspace extends Component
{
    public ?int $selectedIssueId = null;

    public string $searchQuery = '';

    public string $viewScope = 'all';

    public string $statusFilter = '';

    public string $mode = 'view';

    public function mount(?int $issue = null): void
    {
        if ($issue !== null && Issue::query()->whereKey($issue)->exists()) {
            $this->selectedIssueId = $issue;
        } else {
            $this->selectedIssueId = Issue::query()->latest('id')->value('id');
        }
    }

    #[On('workspace-select-issue')]
    public function selectIssue(int $issueId): void
    {
        $this->selectedIssueId = $issueId;
        $this->mode = 'view';
    }

    #[On('workspace-start-create')]
    public function startCreate(): void
    {
        $this->selectedIssueId = null;
        $this->mode = 'create';
    }

    #[On('workspace-search-updated')]
    public function updateSearchQuery(string $value): void
    {
        $this->searchQuery = $value;
    }

    #[On('workspace-scope-updated')]
    public function updateViewScope(string $scope): void
    {
        $this->viewScope = $scope;
    }

    #[On('workspace-status-updated')]
    public function updateStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    #[On('workspace-issue-saved')]
    public function handleIssueSaved(int $issueId): void
    {
        $this->selectedIssueId = $issueId;
        $this->mode = 'view';
    }

    #[On('workspace-issue-deleted')]
    public function handleIssueDeleted(): void
    {
        $this->selectedIssueId = Issue::query()->latest('id')->value('id');
        $this->mode = 'view';
    }

    #[On('workspace-create-cancelled')]
    public function cancelCreate(): void
    {
        $this->selectedIssueId = Issue::query()->latest('id')->value('id');
        $this->mode = 'view';
    }

    #[On('workspace-clear-selection')]
    public function clearSelection(): void
    {
        $this->selectedIssueId = null;
        $this->mode = 'view';
    }

    public function setViewScope(string $scope): void
    {
        $this->viewScope = $scope;
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    public function render()
    {
        return view('livewire.issue-workspace')
            ->layout('components.layouts.app')
            ->title('Issues');
    }
}
