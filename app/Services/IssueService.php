<?php

namespace App\Services;

use App\Models\Issue;

class IssueService
{
    public function createIssue(array $attributes, int $creatorId): Issue
    {
        $issue = new Issue([
            'title' => $attributes['title'],
            'description' => $attributes['description'],
            'status' => $attributes['status'],
            'priority' => $attributes['priority'],
            'category' => $attributes['category'],
            'assigned_to' => $attributes['assignedTo'] !== '' ? (int) $attributes['assignedTo'] : null,
        ]);
        $issue->created_by = $creatorId;
        $issue->save();

        return $issue;
    }

    public function updateIssue(Issue $issue, array $attributes, string $expectedVersion): bool
    {
        return Issue::query()
            ->whereKey($issue->id)
            ->where('updated_at', $expectedVersion)
            ->update([
                'title' => $attributes['title'],
                'description' => $attributes['description'],
                'status' => $attributes['status'],
                'priority' => $attributes['priority'],
                'category' => $attributes['category'],
                'assigned_to' => $attributes['assignedTo'] !== '' ? (int) $attributes['assignedTo'] : null,
                'updated_at' => now(),
            ]) > 0;
    }

    public function deleteIssue(Issue $issue, string $expectedVersion): bool
    {
        return Issue::query()
            ->whereKey($issue->id)
            ->where('updated_at', $expectedVersion)
            ->delete() > 0;
    }
}
