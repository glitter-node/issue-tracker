<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Issue extends Model
{
    public const STATUSES = ['open', 'in_progress', 'closed'];

    public const PRIORITIES = ['low', 'medium', 'high', 'critical'];

    public const CATEGORIES = ['bug', 'feature', 'enhancement'];

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'category',
        'assigned_to',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
