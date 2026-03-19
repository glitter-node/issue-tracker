<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Livewire\IssueDetail;
use App\Livewire\IssueList;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IssueTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_workspace_and_dashboard(): void
    {
        $this->get('/issues')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_user_can_login_with_session_auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('issues.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_workspace_page_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('issues.index'))
            ->assertOk()
            ->assertSee('Search issues')
            ->assertSee('All Issues');
    }

    public function test_issue_can_be_created_inside_workspace_panel(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();

        Livewire::actingAs($creator)
            ->test(IssueDetail::class, ['mode' => 'create'])
            ->set('title', 'Broken search')
            ->set('description', 'The search results are empty.')
            ->set('status', 'open')
            ->set('priority', 'high')
            ->set('category', 'bug')
            ->set('assignedTo', (string) $assignee->id)
            ->call('createIssue')
            ->assertDispatched('workspace-issue-saved');

        $this->assertDatabaseHas('issues', [
            'title' => 'Broken search',
            'description' => 'The search results are empty.',
            'status' => 'open',
            'priority' => 'high',
            'category' => 'bug',
            'created_by' => $creator->id,
            'assigned_to' => $assignee->id,
        ]);
    }

    public function test_issue_list_supports_search_scope_and_status_filters(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();

        Issue::query()->forceCreate([
            'title' => 'Broken search',
            'description' => 'The search results are empty.',
            'status' => 'open',
            'priority' => 'high',
            'category' => 'bug',
            'created_by' => $creator->id,
            'assigned_to' => $assignee->id,
        ]);

        Issue::query()->forceCreate([
            'title' => 'Dashboard cleanup',
            'description' => 'Close out the old metrics.',
            'status' => 'closed',
            'priority' => 'low',
            'category' => 'enhancement',
            'created_by' => $creator->id,
        ]);

        Livewire::actingAs($assignee)
            ->test(IssueList::class, [
                'selectedIssueId' => null,
                'searchQuery' => 'search',
                'viewScope' => 'mine',
                'statusFilter' => 'open',
            ])
            ->assertSee('Broken search')
            ->assertDontSee('Dashboard cleanup');
    }

    public function test_issue_can_be_updated_by_assignee(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $newAssignee = User::factory()->create();

        $issue = Issue::query()->forceCreate([
            'title' => 'Original title',
            'description' => 'Original description.',
            'status' => 'open',
            'priority' => 'low',
            'category' => 'feature',
            'created_by' => $creator->id,
            'assigned_to' => $assignee->id,
        ]);

        Livewire::actingAs($assignee)
            ->test(IssueDetail::class, ['issueId' => $issue->id, 'mode' => 'view'])
            ->set('title', 'Updated title')
            ->set('description', 'Updated description.')
            ->set('status', 'in_progress')
            ->set('priority', 'high')
            ->set('category', 'enhancement')
            ->set('assignedTo', (string) $newAssignee->id)
            ->call('saveIssue')
            ->assertDispatched('workspace-issue-saved');

        $this->assertDatabaseHas('issues', [
            'id' => $issue->id,
            'title' => 'Updated title',
            'description' => 'Updated description.',
            'status' => 'in_progress',
            'priority' => 'high',
            'category' => 'enhancement',
            'assigned_to' => $newAssignee->id,
        ]);
    }

    public function test_stale_issue_update_fails_with_conflict_error(): void
    {
        $creator = User::factory()->create();

        $issue = Issue::query()->forceCreate([
            'title' => 'Stale title',
            'description' => 'Original description.',
            'status' => 'open',
            'priority' => 'medium',
            'category' => 'bug',
            'created_by' => $creator->id,
        ]);

        $component = Livewire::actingAs($creator)
            ->test(IssueDetail::class, ['issueId' => $issue->id, 'mode' => 'view']);

        $issue->forceFill([
            'title' => 'Changed elsewhere',
            'updated_at' => now()->addSecond(),
        ])->save();

        $component
            ->set('title', 'Conflicting title')
            ->call('saveIssue')
            ->assertHasErrors(['conflict']);

        $this->assertDatabaseHas('issues', [
            'id' => $issue->id,
            'title' => 'Changed elsewhere',
        ]);
    }

    public function test_issue_update_is_forbidden_for_unrelated_user(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $otherUser = User::factory()->create();

        $issue = Issue::query()->forceCreate([
            'title' => 'Locked issue',
            'description' => 'This issue should not change.',
            'status' => 'open',
            'priority' => 'medium',
            'category' => 'bug',
            'created_by' => $creator->id,
            'assigned_to' => $assignee->id,
        ]);

        Livewire::actingAs($otherUser)
            ->test(IssueDetail::class, ['issueId' => $issue->id, 'mode' => 'view'])
            ->set('status', 'closed')
            ->call('saveIssue')
            ->assertForbidden();
    }

    public function test_issue_can_only_be_deleted_by_creator(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();

        $issue = Issue::query()->forceCreate([
            'title' => 'Delete me',
            'description' => 'Delete permission check.',
            'status' => 'open',
            'priority' => 'medium',
            'category' => 'bug',
            'created_by' => $creator->id,
            'assigned_to' => $assignee->id,
        ]);

        Livewire::actingAs($assignee)
            ->test(IssueDetail::class, ['issueId' => $issue->id, 'mode' => 'view'])
            ->call('deleteIssue')
            ->assertForbidden();

        Livewire::actingAs($creator)
            ->test(IssueDetail::class, ['issueId' => $issue->id, 'mode' => 'view'])
            ->call('deleteIssue')
            ->assertDispatched('workspace-issue-deleted');

        $this->assertDatabaseMissing('issues', [
            'id' => $issue->id,
        ]);
    }

    public function test_comments_can_be_created_updated_and_deleted_by_author_only(): void
    {
        $creator = User::factory()->create();
        $author = User::factory()->create();
        $otherUser = User::factory()->create();

        $issue = Issue::query()->forceCreate([
            'title' => 'Comment target',
            'description' => 'Review comment permissions.',
            'status' => 'open',
            'priority' => 'medium',
            'category' => 'bug',
            'created_by' => $creator->id,
            'assigned_to' => $author->id,
        ]);

        Livewire::actingAs($author)
            ->test(IssueDetail::class, ['issueId' => $issue->id, 'mode' => 'view'])
            ->set('newComment', 'First comment')
            ->call('addComment');

        $comment = Comment::query()->firstOrFail();

        Livewire::actingAs($author)
            ->test(IssueDetail::class, ['issueId' => $issue->id, 'mode' => 'view'])
            ->call('startEditingComment', $comment->id)
            ->set('editingComment', 'Updated comment')
            ->call('updateComment');

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated comment',
            'user_id' => $author->id,
        ]);

        Livewire::actingAs($otherUser)
            ->test(IssueDetail::class, ['issueId' => $issue->id, 'mode' => 'view'])
            ->call('deleteComment', $comment->id)
            ->assertForbidden();

        Livewire::actingAs($author)
            ->test(IssueDetail::class, ['issueId' => $issue->id, 'mode' => 'view'])
            ->call('deleteComment', $comment->id);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_dashboard_renders_metrics_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        Issue::query()->forceCreate([
            'title' => 'Latest issue',
            'description' => 'Newest dashboard row.',
            'status' => 'open',
            'priority' => 'high',
            'category' => 'feature',
            'created_by' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee('Dashboard')
            ->assertSee('Latest issue')
            ->assertSee('feature');
    }
}
