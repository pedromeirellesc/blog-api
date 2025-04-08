<?php

namespace App\Providers;

use App\Policies\CommentPolicy;
use App\Policies\PostPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Gate::define('comment-delete', [CommentPolicy::class, 'delete']);
        Gate::define('post-delete', [PostPolicy::class, 'delete']);
    }
}
