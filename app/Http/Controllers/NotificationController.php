<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark the branch notification feed as read for the current session.
     * The bell badge counts anything newer than this timestamp.
     */
    public function markRead(Request $request)
    {
        $request->session()->put('notifications_read_at', now()->toIso8601String());

        return back();
    }
}
