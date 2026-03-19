<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Issue;
use App\Models\User;
use App\Services\CommentService;
use App\Services\IssueService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class IssueDetail extends Component
{
    #[Reactive]
    public ?int $issueId = null;

    #[Reactive]
    public string $mode = 'view';

    public string $title = '';

    public string $description = '';

    public string $status = 'open';

    public string $priority = 'medium';

    public string $category = 'bug';

    public string $assignedTo = '';

    public string $newComment = '';

    public ?int $editingCommentId = null;

    public string $editingComment = '';

    public ?int $loadedIssueId = null;

    public string $loadedMode = 'view';

    public bool $isSaving = false;

    public int $commentsLimit = 20;

    public array $assignees = [];

    public string $issueVersion = '';

    public bool $isStale = false;

    public ?string $editingCommentVersion = null;

    private ?Issue $cachedIssue = null;

    private ?int $cachedIssueKey = null;

    public function saveTitle(): void
    {
        $this->saveIssue();
    }

    public function saveDescription(): void
    {
        $this->saveIssue();
    }

    public function saveStatus(): void
    {
        $this->saveIssue();
    }

    public function savePriority(): void
    {
        $this->saveIssue();
    }

    public function saveCategory(): void
    {
        $this->saveIssue();
    }

    public function saveAssignee(): void
    {
        $this->saveIssue();
    }

    public function createIssue(): void
    {
        abort_unless(auth()->check(), 403);

        $this->isSaving = true;
        $validated = $this->validate($this->issueRules());

        $issue = app(IssueService::class)->createIssue($validated, (int) auth()->id());

        $this->resetErrorBag();
        $this->isSaving = false;
        $this->cachedIssue = null;
        $this->cachedIssueKey = null;
        $this->refreshIssueState($issue->id);
        $this->dispatch('workspace-issue-saved', issueId: $issue->id);
    }

    public function saveIssue(): void
    {
        $issue = $this->currentIssue();

        abort_if($issue === null, 404);
        abort_if(Gate::denies('update', $issue), 403);

        if ($this->issueHasConflict()) {
            return;
        }

        $this->isSaving = true;
        $validated = $this->validate($this->issueRules());

        $updated = app(IssueService::class)->updateIssue($issue, $validated, $this->issueVersion);

        if (! $updated) {
            $this->markIssueConflict();

            return;
        }

        $this->resetErrorBag();
        $this->isSaving = false;
        $this->cachedIssue = null;
        $this->cachedIssueKey = null;
        $this->refreshIssueState($issue->id);
        $this->dispatch('workspace-issue-saved', issueId: $issue->id);
    }

    public function deleteIssue(): void
    {
        $issue = $this->currentIssue();

        abort_if($issue === null, 404);
        abort_if(Gate::denies('delete', $issue), 403);

        if ($this->issueHasConflict()) {
            return;
        }

        $deleted = app(IssueService::class)->deleteIssue($issue, $this->issueVersion);

        if (! $deleted) {
            $this->markIssueConflict();

            return;
        }

        $this->cachedIssue = null;
        $this->cachedIssueKey = null;
        $this->dispatch('workspace-issue-deleted');
    }

    public function cancelCreate(): void
    {
        $this->dispatch('workspace-create-cancelled');
    }

    public function mount(): void
    {
        $this->assignees = User::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])
            ->all();
    }

    public function addComment(): void
    {
        abort_if($this->issueId === null, 404);
        abort_unless(auth()->check(), 403);
        abort_unless(Issue::query()->whereKey($this->issueId)->exists(), 404);

        $this->refreshStaleState();

        $validated = $this->validate([
            'newComment' => ['required', 'string', 'max:1000'],
        ]);

        app(CommentService::class)->createComment($this->issueId, (int) auth()->id(), $validated['newComment']);

        $this->reset('newComment');
        $this->commentsLimit = max($this->commentsLimit, 20);
        $this->dispatch('comment-added');
    }

    public function startEditingComment(int $commentId): void
    {
        $comment = $this->commentQuery()->findOrFail($commentId);

        abort_if(Gate::denies('update', $comment), 403);

        $this->editingCommentId = $comment->id;
        $this->editingComment = $comment->content;
        $this->editingCommentVersion = (string) $comment->getRawOriginal('updated_at');
    }

    public function updateComment(): void
    {
        $comment = $this->commentQuery()->findOrFail($this->editingCommentId);

        abort_if(Gate::denies('update', $comment), 403);

        $validated = $this->validate([
            'editingComment' => ['required', 'string', 'max:1000'],
        ]);

        $updated = app(CommentService::class)->updateComment(
            $comment,
            (int) $this->issueId,
            $validated['editingComment'],
            (string) $this->editingCommentVersion,
        );

        if (! $updated) {
            Log::warning('Comment update conflict detected.', [
                'comment_id' => $comment->id,
                'issue_id' => $this->issueId,
                'user_id' => auth()->id(),
            ]);

            $this->addError('commentConflict', 'This comment was updated by another user. Please refresh.');

            return;
        }

        $this->editingCommentId = null;
        $this->editingComment = '';
        $this->editingCommentVersion = null;
    }

    public function cancelCommentEdit(): void
    {
        $this->editingCommentId = null;
        $this->editingComment = '';
        $this->editingCommentVersion = null;
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->commentQuery()->findOrFail($commentId);

        abort_if(Gate::denies('delete', $comment), 403);

        $deleted = app(CommentService::class)->deleteComment(
            $comment,
            (int) $this->issueId,
            (string) $comment->getRawOriginal('updated_at'),
        );

        if (! $deleted) {
            Log::warning('Comment delete conflict detected.', [
                'comment_id' => $comment->id,
                'issue_id' => $this->issueId,
                'user_id' => auth()->id(),
            ]);

            $this->addError('commentConflict', 'This comment was updated by another user. Please refresh.');
        }
    }

    public function loadMoreComments(): void
    {
        $this->commentsLimit += 20;
    }

    public function refreshIssue(): void
    {
        abort_if($this->issueId === null, 404);

        $this->refreshIssueState($this->issueId);
    }

    public function render()
    {
        $issue = $this->currentIssue();
        $this->syncState($issue);

        return view('livewire.issue-detail', [
            'issue' => $issue?->loadMissing([
                'creator:id,name',
                'assignee:id,name',
            ]),
            'assignees' => $this->assignees,
            'comments' => $issue === null
                ? null
                : $issue->comments()
                    ->with('user:id,name')
                    ->latest('id')
                    ->limit($this->commentsLimit)
                    ->get(),
            'hasMoreComments' => $issue !== null && $issue->comments()->count() > $this->commentsLimit,
            'canUpdate' => $issue !== null && auth()->check() && Gate::allows('update', $issue),
            'canDelete' => $issue !== null && auth()->check() && Gate::allows('delete', $issue),
            'statuses' => Issue::STATUSES,
            'priorities' => Issue::PRIORITIES,
            'categories' => Issue::CATEGORIES,
        ]);
    }

    #[On('workspace-select-issue')]
    #[On('workspace-start-create')]
    #[On('workspace-create-cancelled')]
    #[On('workspace-issue-saved')]
    public function resetSavingState(): void
    {
        $this->isSaving = false;
    }

    private function syncState(?Issue $issue): void
    {
        if ($this->mode === 'create') {
            if ($this->loadedMode !== 'create') {
                $this->resetIssueForm();
                $this->loadedIssueId = null;
                $this->loadedMode = 'create';
                $this->commentsLimit = 20;
            }

            return;
        }

        if ($this->issueId === null) {
            $this->loadedIssueId = null;
            $this->loadedMode = 'view';
            $this->commentsLimit = 20;

            return;
        }

        if ($this->loadedIssueId === $this->issueId && $this->loadedMode === 'view') {
            return;
        }

        if ($issue === null) {
            return;
        }

        $this->title = $issue->title;
        $this->description = $issue->description;
        $this->status = $issue->status;
        $this->priority = $issue->priority;
        $this->category = $issue->category;
        $this->assignedTo = $issue->assigned_to !== null ? (string) $issue->assigned_to : '';
        $this->editingCommentId = null;
        $this->editingComment = '';
        $this->issueVersion = (string) $issue->getRawOriginal('updated_at');
        $this->isStale = false;
        $this->loadedIssueId = $issue->id;
        $this->loadedMode = 'view';
        $this->commentsLimit = 20;
    }

    private function resetIssueForm(): void
    {
        $this->resetErrorBag();
        $this->title = '';
        $this->description = '';
        $this->status = Issue::STATUSES[0];
        $this->priority = Issue::PRIORITIES[1];
        $this->category = Issue::CATEGORIES[0];
        $this->assignedTo = '';
        $this->newComment = '';
        $this->editingCommentId = null;
        $this->editingComment = '';
        $this->editingCommentVersion = null;
        $this->issueVersion = '';
        $this->isStale = false;
        $this->commentsLimit = 20;
    }

    private function currentIssue(): ?Issue
    {
        if ($this->issueId === null) {
            $this->cachedIssue = null;
            $this->cachedIssueKey = null;

            return null;
        }

        if ($this->cachedIssueKey === $this->issueId && $this->cachedIssue !== null) {
            return $this->cachedIssue;
        }

        $this->cachedIssue = Issue::query()->with([
            'creator:id,name',
            'assignee:id,name',
        ])->find($this->issueId);
        $this->cachedIssueKey = $this->issueId;

        return $this->cachedIssue;
    }

    private function commentQuery()
    {
        abort_if($this->issueId === null, 404);

        return Comment::query()->where('issue_id', $this->issueId);
    }

    private function latestIssueVersion(): ?string
    {
        if ($this->issueId === null) {
            return null;
        }

        $value = Issue::query()
            ->whereKey($this->issueId)
            ->value('updated_at');

        return $value !== null ? (string) $value : null;
    }

    private function refreshStaleState(): void
    {
        $latestVersion = $this->latestIssueVersion();
        $this->isStale = $this->issueVersion !== '' && $latestVersion !== null && $latestVersion !== $this->issueVersion;
    }

    private function issueHasConflict(): bool
    {
        $this->refreshStaleState();

        if (! $this->isStale) {
            return false;
        }

        $this->markIssueConflict();

        return true;
    }

    private function markIssueConflict(): void
    {
        $this->isSaving = false;
        Log::warning('Issue conflict detected.', [
            'issue_id' => $this->issueId,
            'user_id' => auth()->id(),
        ]);
        $this->addError('conflict', 'This issue was updated by another user. Please reload.');
    }

    private function refreshIssueState(int $issueId): void
    {
        $this->cachedIssue = null;
        $this->cachedIssueKey = null;

        $issue = Issue::query()->with([
            'creator:id,name',
            'assignee:id,name',
        ])->find($issueId);

        if ($issue === null) {
            return;
        }

        $this->resetErrorBag();
        $this->syncState($issue);
    }

    private function issueRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => ['required', Rule::in(Issue::STATUSES)],
            'priority' => ['required', Rule::in(Issue::PRIORITIES)],
            'category' => ['required', Rule::in(Issue::CATEGORIES)],
            'assignedTo' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
