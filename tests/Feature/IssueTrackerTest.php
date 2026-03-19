<?php

namespace Tests\Feature;

use App\Jobs\CommentCreatedJob;
use App\Livewire\Dashboard;
use App\Livewire\IssueDetail;
use App\Livewire\IssueList;
use App\Mail\RegistrationVerificationMail;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class IssueTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_access_the_public_landing_page(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('<title>Home - GLITTER ISSUE TRACKER</title>', false)
            ->assertSee('Simple issue tracking for small teams')
            ->assertSee('Get started')
            ->assertSee('Use case')
            ->assertSee('Start tracking your work in minutes.')
            ->assertSee(route('login'), false)
            ->assertSee(route('register.email'), false);
    }

    public function test_authenticated_users_are_redirected_from_landing_page_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('landing'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_authenticated_layout_shows_current_user_identity_and_logout(): void
    {
        $user = User::factory()->create([
            'name' => 'Session Visible User',
            'email' => 'visible@example.com',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Session Visible User')
            ->assertSee('visible@example.com')
            ->assertSee('Logout');

        $this->get(route('landing'))
            ->assertDontSee('visible@example.com');
    }

    public function test_guests_are_redirected_from_workspace_and_dashboard(): void
    {
        $this->get('/issues')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_session_expired_message_is_shown_after_guest_redirect_to_login(): void
    {
        $this->followingRedirects()
            ->get(route('issues.index'))
            ->assertOk()
            ->assertSee('Your session expired. Please continue.');
    }

    public function test_user_can_login_with_session_auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_page_uses_consistent_title_format(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<title>Login - GLITTER ISSUE TRACKER</title>', false);
    }

    public function test_login_redirects_to_intended_protected_page(): void
    {
        $user = User::factory()->create();

        $this->get(route('issues.index'))->assertRedirect(route('login'));

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('issues.index'));
    }

    public function test_failed_login_preserves_email_and_remember_but_not_password(): void
    {
        $user = User::factory()->create();

        $response = $this->from(route('login'))->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'wrong-password',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'auth' => 'Invalid email or password.',
        ]);
        $response->assertSessionHasInput([
            'email' => $user->email,
            'remember' => '1',
        ]);
        $response->assertSessionMissing('_old_input.password');

        $this->followingRedirects()->from(route('login'))->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'wrong-password',
            'remember' => '1',
        ])
            ->assertOk()
            ->assertDontSee('Your session expired. Please continue.')
            ->assertSee('Invalid email or password.')
            ->assertSee('value="'.$user->email.'"', false)
            ->assertSee('checked', false)
            ->assertDontSee('wrong-password');
    }

    public function test_password_reset_flow_sends_email_and_allows_login_with_new_password(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'password' => 'old-password',
        ]);

        $this->get(route('password.request'))->assertOk();

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertSessionHas('status');

        $token = null;

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return $token !== null;
        });

        $this->get(route('password.reset', ['token' => $token]).'?email='.urlencode($user->email))
            ->assertOk();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login'));

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'new-password',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_login_cancel_defaults_to_landing_page_on_direct_access(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Your session expired. Please continue.')
            ->assertSee('href="'.route('landing').'"', false);
    }

    public function test_login_cancel_uses_safe_internal_previous_url(): void
    {
        $this->withHeader('referer', route('landing'))
            ->get(route('login'))
            ->assertOk()
            ->assertSee('href="'.route('landing').'"', false);

        $this->withHeader('referer', 'https://evil.example/login')
            ->get(route('login'))
            ->assertOk()
            ->assertSee('href="'.route('landing').'"', false);

        $this->withHeader('referer', route('issues.index'))
            ->get(route('login'))
            ->assertOk()
            ->assertSee('href="'.route('landing').'"', false);
    }

    public function test_verify_email_cancel_defaults_to_landing_page_on_direct_access(): void
    {
        $this->get(route('register.email'))
            ->assertOk()
            ->assertSee('href="'.route('landing').'"', false);
    }

    public function test_verify_email_cancel_uses_safe_internal_previous_url(): void
    {
        $this->withHeader('referer', route('landing'))
            ->get(route('register.email'))
            ->assertOk()
            ->assertSee('href="'.route('landing').'"', false);

        $this->withHeader('referer', 'https://evil.example/register/email')
            ->get(route('register.email'))
            ->assertOk()
            ->assertSee('href="'.route('landing').'"', false);
    }

    public function test_registration_requires_verified_email_before_access(): void
    {
        $this->get(route('register'))
            ->assertRedirect(route('register.email'));

        $this->post(route('register.store'), [
            'name' => 'Unverified User',
            'password' => 'password',
            'password_confirmation' => 'password',
            'email' => 'attacker@example.com',
        ])->assertRedirect(route('register.email'));
    }

    public function test_registration_flow_requires_email_verification_and_uses_session_email_only(): void
    {
        Mail::fake();

        $email = 'verifyme@example.com';

        $this->post(route('register.email.send'), [
            'email' => $email,
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('email_verifications', [
            'email' => $email,
            'verified_at' => null,
        ]);

        Mail::assertSent(RegistrationVerificationMail::class, function (RegistrationVerificationMail $mail) use ($email): bool {
            return $mail->hasTo($email);
        });

        $token = DB::table('email_verifications')->where('email', $email)->value('token');

        $this->get(route('register.verify', ['token' => $token]))
            ->assertRedirect(route('register'));

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('value="'.$email.'"', false)
            ->assertSee('disabled', false);

        $this->post(route('register.store'), [
            'name' => 'Verified User',
            'email' => 'attacker@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'name' => 'Verified User',
            'email' => $email,
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'attacker@example.com',
        ]);

        $this->assertDatabaseMissing('email_verifications', [
            'email' => $email,
        ]);
    }

    public function test_workspace_page_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('issues.index'))
            ->assertOk()
            ->assertSee('<title>Issues - GLITTER ISSUE TRACKER</title>', false)
            ->assertSee('Search issues')
            ->assertSee('All Issues');
    }

    public function test_issue_list_shows_first_time_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('issues.index'))
            ->assertOk()
            ->assertSee('No issues yet.')
            ->assertSee('Create your first issue to get started.')
            ->assertSee('Create Issue');
    }

    public function test_issue_can_be_created_inside_workspace_panel(): void
    {
        Queue::fake();

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

        Queue::assertNothingPushed();
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
        Queue::fake();

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

        Queue::assertPushed(CommentCreatedJob::class, function (CommentCreatedJob $job) use ($comment): bool {
            return $job->comment->is($comment);
        });

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
