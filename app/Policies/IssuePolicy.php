<?php

namespace App\Policies;

use App\Models\Issue;
use App\Models\User;

class IssuePolicy
{
    public function update(User $user, Issue $issue): bool
    {
        return $issue->created_by === $user->id || $issue->assigned_to === $user->id;
    }

    public function delete(User $user, Issue $issue): bool
    {
        return $issue->created_by === $user->id;
    }
}
