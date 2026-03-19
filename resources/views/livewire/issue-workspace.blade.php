<section class="grid gap-6 xl:grid-cols-[240px_minmax(0,1fr)_420px]">
    <aside class="space-y-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Views</h2>
            <div class="mt-4 space-y-2">
                <button class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-medium transition {{ $viewScope === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}" type="button" wire:click="setViewScope('all')">
                    <span>All Issues</span>
                </button>
                <button class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-medium transition {{ $viewScope === 'mine' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}" type="button" wire:click="setViewScope('mine')">
                    <span>My Issues</span>
                </button>
                <button class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-medium transition {{ $viewScope === 'created' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}" type="button" wire:click="setViewScope('created')">
                    <span>Created by Me</span>
                </button>
            </div>
        </div>

        <div>
            <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Status</h2>
            <div class="mt-4 space-y-2">
                <button class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-medium transition {{ $statusFilter === '' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}" type="button" wire:click="setStatusFilter('')">
                    <span>All Statuses</span>
                </button>
                @foreach (\App\Models\Issue::STATUSES as $status)
                    <button class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-medium transition {{ $statusFilter === $status ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}" type="button" wire:click="setStatusFilter('{{ $status }}')">
                        <span>{{ str_replace('_', ' ', $status) }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </aside>

    <section class="space-y-4">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1">
                    <label class="mb-2 block text-sm font-medium text-slate-600" for="workspace-search">Search issues</label>
                    <input class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-slate-900 focus:outline-none focus:ring-0" id="workspace-search" type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search by title or description">
                </div>
                <div class="w-full lg:w-52">
                    <label class="mb-2 block text-sm font-medium text-slate-600" for="workspace-status">Quick status</label>
                    <select class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-slate-900 focus:outline-none focus:ring-0" id="workspace-status" wire:model.live="statusFilter">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\Issue::STATUSES as $status)
                            <option value="{{ $status }}">{{ str_replace('_', ' ', $status) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <livewire:issue-list
            :selected-issue-id="$selectedIssueId"
            :search-query="$searchQuery"
            :view-scope="$viewScope"
            :status-filter="$statusFilter"
            :key="'issue-list-'.$selectedIssueId.'-'.$viewScope.'-'.$statusFilter.'-'.md5($searchQuery)"
        />
    </section>

    <section>
        <livewire:issue-detail
            :issue-id="$selectedIssueId"
            :mode="$mode"
            :key="'issue-detail-'.($selectedIssueId ?? 'new').'-'.$mode"
        />
    </section>
</section>
