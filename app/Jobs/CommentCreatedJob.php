<?php

namespace App\Jobs;

use App\Models\Comment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CommentCreatedJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Comment $comment) {}

    // Jobs are limited to non-critical side effects. Business mutations must
    // complete before dispatch, so this job only records activity-style output.
    public function handle(): void
    {
        Log::warning('Comment created job processed.', [
            'comment_id' => $this->comment->id,
        ]);
    }
}
