<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Messages;
use Illuminate\Support\Facades\View;

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
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $unreadCount = Messages::where('is_read', false)->count();
                $view->with('unreadMessagesCount', $unreadCount);
            }
        });
    }
}
