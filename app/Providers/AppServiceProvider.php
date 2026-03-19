<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Issue;
use App\Policies\CommentPolicy;
use App\Policies\IssuePolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Issue::class, IssuePolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);

        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(strtolower($request->string('email')->value()).'|'.$request->ip());
        });
    }
}
