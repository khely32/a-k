<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Branch;
use Symfony\Component\HttpFoundation\Response;

class CheckBranchActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->role !== 'owner' && !empty($user->branch_id)) {
            $branch = Branch::find($user->branch_id);

            if ($branch && !$branch->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Your branch has been disabled by management.',
                    ], 403);
                }

                return redirect()->route('login')->withErrors([
                    'email' => 'Your branch is currently disabled by the owner. System access is denied.',
                ]);
            }
        }

        return $next($request);
    }
}
