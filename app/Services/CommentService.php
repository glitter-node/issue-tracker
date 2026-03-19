<?php

namespace App\Services;

use App\Jobs\CommentCreatedJob;
use App\Models\Comment;

class CommentService
{
    // Core comment persistence stays synchronous; only non-critical follow-up
    // side effects are deferred via the single comment-created job.
    public function createComment(int $issueId, int $userId, string $content): Comment
    {
        $comment = new Comment([
            'content' => $content,
        ]);
        $comment->issue_id = $issueId;
        $comment->user_id = $userId;
        $comment->save();

        CommentCreatedJob::dispatch($comment);

        return $comment;
    }

    public function updateComment(Comment $comment, int $issueId, string $content, string $expectedVersion): bool
    {
        return Comment::query()
            ->whereKey($comment->id)
            ->where('issue_id', $issueId)
            ->where('updated_at', $expectedVersion)
            ->update([
                'content' => $content,
                'updated_at' => now(),
            ]) > 0;
    }

    public function deleteComment(Comment $comment, int $issueId, string $expectedVersion): bool
    {
        return Comment::query()
            ->whereKey($comment->id)
            ->where('issue_id', $issueId)
            ->where('updated_at', $expectedVersion)
            ->delete() > 0;
    }
}
