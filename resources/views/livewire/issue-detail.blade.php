<section class="relative rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="absolute inset-0 z-10 hidden items-center justify-center rounded-2xl bg-white/80 backdrop-blur-sm" wire:loading.flex>
        <div class="space-y-3 text-center">
            <div class="mx-auto h-10 w-10 animate-spin rounded-full border-2 border-slate-200 border-t-slate-900"></div>
            <p class="text-sm font-medium text-slate-500">Loading issue workspace...</p>
        </div>
    </div>

    @if ($mode === 'create')
        <header class="border-b border-slate-200 px-5 py-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">New Issue</h2>
                    <p class="text-sm text-slate-500">Create a new issue without leaving the workspace.</p>
                </div>
                <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100" type="button" wire:click="cancelCreate">
                    Cancel
                </button>
            </div>
        </header>

        <div class="space-y-5 p-5" wire:loading.remove>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700" for="create-title">Title</label>
                <input class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60" id="create-title" type="text" wire:model.live="title" wire:loading.attr="disabled">
                @error('title')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700" for="create-status">Status</label>
                    <select class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60" id="create-status" wire:model.live="status" wire:loading.attr="disabled">
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption }}">{{ str_replace('_', ' ', $statusOption) }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700" for="create-priority">Priority</label>
                    <select class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60" id="create-priority" wire:model.live="priority" wire:loading.attr="disabled">
                        @foreach ($priorities as $priorityOption)
                            <option value="{{ $priorityOption }}">{{ $priorityOption }}</option>
                        @endforeach
                    </select>
                    @error('priority')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700" for="create-category">Category</label>
                    <select class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60" id="create-category" wire:model.live="category" wire:loading.attr="disabled">
                        @foreach ($categories as $categoryOption)
                            <option value="{{ $categoryOption }}">{{ $categoryOption }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700" for="create-assignee">Assignee</label>
                <select class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60" id="create-assignee" wire:model.live="assignedTo" wire:loading.attr="disabled">
                    <option value="">Unassigned</option>
                    @foreach ($assignees as $assignee)
                        <option value="{{ $assignee['id'] }}">{{ $assignee['name'] }}</option>
                    @endforeach
                </select>
                @error('assignedTo')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700" for="create-description">Description</label>
                <textarea class="min-h-40 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60" id="create-description" wire:model.live="description" wire:loading.attr="disabled"></textarea>
                @error('description')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <button class="w-full rounded-lg bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-60" type="button" wire:click="createIssue" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="createIssue">Save Issue</span>
                <span wire:loading wire:target="createIssue">Saving issue...</span>
            </button>
        </div>
    @elseif ($issue)
        <header class="border-b border-slate-200 px-5 py-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">Issue #{{ $issue->id }}</p>
                    <p class="mt-1 text-sm text-slate-500">Created by {{ $issue->creator->name }} · {{ $issue->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60" type="button" wire:click="refreshIssue" wire:loading.attr="disabled">
                        Refresh
                    </button>
                    @if ($canDelete)
                        <button class="rounded-lg border border-rose-200 px-4 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50" type="button" wire:click="deleteIssue" wire:confirm="Delete this issue?">
                            Delete
                        </button>
                    @endif
                </div>
            </div>
        </header>

        <div class="space-y-6 p-5" wire:loading.remove>
            @error('conflict')
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    {{ $message }}
                </div>
            @enderror

            @if ($isStale && ! $errors->has('conflict'))
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    This issue may be outdated.
                </div>
            @endif

            <div>
                <input class="w-full border-0 bg-transparent px-0 text-2xl font-bold text-slate-950 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60 {{ $canUpdate ? '' : 'cursor-default' }}" type="text" wire:model.live="title" wire:blur="saveTitle" wire:loading.attr="disabled" @disabled(! $canUpdate)>
                @error('title')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500" for="issue-status">Status</label>
                    <select class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60" id="issue-status" wire:model.live="status" wire:change="saveStatus" wire:loading.attr="disabled" @disabled(! $canUpdate)>
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption }}">{{ str_replace('_', ' ', $statusOption) }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500" for="issue-priority">Priority</label>
                    <select class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60" id="issue-priority" wire:model.live="priority" wire:change="savePriority" wire:loading.attr="disabled" @disabled(! $canUpdate)>
                        @foreach ($priorities as $priorityOption)
                            <option value="{{ $priorityOption }}">{{ $priorityOption }}</option>
                        @endforeach
                    </select>
                    @error('priority')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500" for="issue-category">Category</label>
                    <select class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60" id="issue-category" wire:model.live="category" wire:change="saveCategory" wire:loading.attr="disabled" @disabled(! $canUpdate)>
                        @foreach ($categories as $categoryOption)
                            <option value="{{ $categoryOption }}">{{ $categoryOption }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500" for="issue-assignee">Assignee</label>
                    <select class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60" id="issue-assignee" wire:model.live="assignedTo" wire:change="saveAssignee" wire:loading.attr="disabled" @disabled(! $canUpdate)>
                        <option value="">Unassigned</option>
                        @foreach ($assignees as $assignee)
                            <option value="{{ $assignee['id'] }}">{{ $assignee['name'] }}</option>
                        @endforeach
                    </select>
                    @error('assignedTo')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Description</p>
                <textarea class="mt-3 min-h-44 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60" wire:model.live="description" wire:blur="saveDescription" wire:loading.attr="disabled" @disabled(! $canUpdate)></textarea>
                @error('description')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
                <div class="mt-3 text-xs text-slate-500">
                    <span wire:loading.remove wire:target="saveTitle,saveStatus,savePriority,saveCategory,saveAssignee,saveDescription">Changes sync automatically.</span>
                    <span wire:loading wire:target="saveTitle,saveStatus,savePriority,saveCategory,saveAssignee,saveDescription">Saving changes...</span>
                </div>
            </div>

            <section class="space-y-4">
                <header>
                    <h3 class="text-lg font-semibold text-slate-950">Comments</h3>
                    <p class="text-sm text-slate-500">Discuss progress directly on the issue.</p>
                </header>

                <div>
                    <textarea class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60" wire:model.live="newComment" wire:keydown.enter.prevent="addComment" wire:loading.attr="disabled" data-comment-input placeholder="Add a comment"></textarea>
                    @error('newComment')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                    @error('commentConflict')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                    <button class="mt-3 rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-600 disabled:cursor-not-allowed disabled:opacity-60" type="button" wire:click="addComment" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="addComment">Add Comment</span>
                        <span wire:loading wire:target="addComment">Posting...</span>
                    </button>
                </div>

                <div class="space-y-3" data-comments-list>
                    @forelse ($comments as $comment)
                        <article class="rounded-2xl border border-slate-200 bg-white p-4" data-comment-item wire:key="detail-comment-{{ $comment->id }}">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $comment->user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $comment->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @can('update', $comment)
                                        <button class="rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-950" type="button" wire:click="startEditingComment({{ $comment->id }})">
                                            Edit
                                        </button>
                                    @endcan
                                    @can('delete', $comment)
                                        <button class="rounded-md px-3 py-1.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50" type="button" wire:click="deleteComment({{ $comment->id }})" wire:confirm="Delete this comment?">
                                            Delete
                                        </button>
                                    @endcan
                                </div>
                            </div>

                            @if ($editingCommentId === $comment->id)
                                <div class="mt-3">
                                    <textarea class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60" wire:model.live="editingComment" wire:loading.attr="disabled"></textarea>
                                    @error('editingComment')
                                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                    @enderror
                                    <div class="mt-3 flex items-center gap-2">
                                        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-60" type="button" wire:click="updateComment" wire:loading.attr="disabled">
                                            Save
                                        </button>
                                        <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60" type="button" wire:click="cancelCommentEdit" wire:loading.attr="disabled">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            @else
                                <p class="mt-3 text-sm leading-6 text-slate-700">{{ $comment->content }}</p>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500">
                            No comments yet.
                        </div>
                    @endforelse
                </div>

                @if ($hasMoreComments)
                    <div>
                        <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60" type="button" wire:click="loadMoreComments" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="loadMoreComments">Load more comments</span>
                            <span wire:loading wire:target="loadMoreComments">Loading more...</span>
                        </button>
                    </div>
                @endif
            </section>
        </div>
    @else
        <div class="flex min-h-[32rem] items-center justify-center p-6">
            <div class="max-w-sm text-center">
                <h2 class="text-lg font-semibold text-slate-950">Select an issue to view details</h2>
                <p class="mt-2 text-sm text-slate-500">Choose an issue from the list or create a new issue to start working in the panel.</p>
            </div>
        </div>
    @endif
</section>
