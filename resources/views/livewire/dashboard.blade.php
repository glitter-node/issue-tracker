<section class="space-y-6">
    <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-semibold text-slate-950">Dashboard</h2>
        <p class="mt-1 text-sm text-slate-500">High-level issue metrics and recent activity.</p>
    </header>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total Issues</p>
            <p class="mt-3 text-4xl font-semibold tracking-tight text-slate-950">{{ $totalIssues }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Open</p>
            <p class="mt-3 text-4xl font-semibold tracking-tight text-slate-950">{{ $statusCounts['open'] }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">In Progress</p>
            <p class="mt-3 text-4xl font-semibold tracking-tight text-slate-950">{{ $statusCounts['in_progress'] }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Closed</p>
            <p class="mt-3 text-4xl font-semibold tracking-tight text-slate-950">{{ $statusCounts['closed'] }}</p>
        </article>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <header class="mb-4">
                <h3 class="text-lg font-semibold text-slate-950">By Status</h3>
            </header>
            <div class="space-y-4">
                @foreach ($statusCounts as $label => $count)
                    <div wire:key="status-{{ $label }}">
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">{{ str_replace('_', ' ', $label) }}</span>
                            <span class="text-slate-500">{{ $count }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-slate-900 {{ $statusWidths[$label] }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <header class="mb-4">
                <h3 class="text-lg font-semibold text-slate-950">By Priority</h3>
            </header>
            <div class="space-y-4">
                @foreach ($priorityCounts as $label => $count)
                    <div wire:key="priority-{{ $label }}">
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">{{ $label }}</span>
                            <span class="text-slate-500">{{ $count }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-amber-500 {{ $priorityWidths[$label] }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <header class="mb-4">
                <h3 class="text-lg font-semibold text-slate-950">By Category</h3>
            </header>
            <div class="space-y-4">
                @foreach ($categoryCounts as $label => $count)
                    <div wire:key="category-{{ $label }}">
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-medium capitalize text-slate-700">{{ $label }}</span>
                            <span class="text-slate-500">{{ $count }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-sky-500 {{ $categoryWidths[$label] }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>
    </div>

    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <header class="mb-4 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-950">Latest 5 Issues</h3>
                <p class="mt-1 text-sm text-slate-500">Most recently created issues.</p>
            </div>
        </header>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-600">Title</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600">Priority</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600">Category</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600">Creator</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600">Assignee</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($latestIssues as $issue)
                        <tr wire:key="latest-issue-{{ $issue->id }}">
                            <td class="px-4 py-3 font-medium text-slate-900">
                                <a class="transition hover:text-slate-600" href="{{ route('issues.index', ['issue' => $issue->id]) }}">{{ $issue->title }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ str_replace('_', ' ', $issue->status) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $issue->priority }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $issue->category }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $issue->creator->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $issue->assignee?->name ?? 'Unassigned' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">No issues yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
</section>
