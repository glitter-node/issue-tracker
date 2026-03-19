<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
        <div>
            <h2 class="text-lg font-semibold text-slate-950">Issues</h2>
            <p class="text-sm text-slate-500">Browse, filter, and open issues in the workspace.</p>
        </div>
        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700" type="button" wire:click="startCreate">
            + New Issue
        </button>
    </header>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left font-medium text-slate-500">Title</th>
                    <th class="px-5 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-5 py-3 text-left font-medium text-slate-500">Priority</th>
                    <th class="px-5 py-3 text-left font-medium text-slate-500">Assignee</th>
                    <th class="px-5 py-3 text-left font-medium text-slate-500">Updated</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse ($issues as $issue)
                    <tr class="cursor-pointer border-l-4 transition hover:bg-slate-50 {{ $selectedIssueId === $issue->id ? 'border-sky-500 bg-sky-50/80' : 'border-transparent' }}" wire:key="issue-row-{{ $issue->id }}" wire:click="selectIssue({{ $issue->id }})">
                        <td class="px-5 py-4">
                            <div class="font-medium {{ $selectedIssueId === $issue->id ? 'text-slate-950' : 'text-slate-900' }}">{{ $issue->title }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $issue->category }} · {{ $issue->creator->name }}</div>
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ str_replace('_', ' ', $issue->status) }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $issue->priority }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $issue->assignee?->name ?? 'Unassigned' }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $issue->updated_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-10 text-center" colspan="5">
                            @if (! $hasAnyIssues)
                                <div class="space-y-4">
                                    <div>
                                        <p class="font-medium text-slate-900">No issues yet.</p>
                                        <p class="mt-1 text-slate-500">Create your first issue to get started.</p>
                                    </div>
                                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700" type="button" wire:click="startCreate">
                                        Create Issue
                                    </button>
                                </div>
                            @else
                                <p class="text-slate-500">No issues match the current filters.</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-200 px-5 py-4">
        {{ $issues->links() }}
    </div>
</section>
