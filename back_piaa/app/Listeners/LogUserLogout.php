<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Logout;

class LogUserLogout
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Logout $event)
    {
        $event->user->logins()
            ->where('session_id', session()->getId())
            ->where('is_active', true)
            ->update([
                'logged_out_at' => now(),
                'is_active' => false
            ]);
    }
}
