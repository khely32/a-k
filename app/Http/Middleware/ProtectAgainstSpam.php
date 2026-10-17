<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Honeypot\Events\SpamDetectedEvent;
use Spatie\Honeypot\Exceptions\SpamException;
use Spatie\Honeypot\ProtectAgainstSpam as SpatieProtectAgainstSpam;
use Spatie\Honeypot\SpamProtection;

class ProtectAgainstSpam extends SpatieProtectAgainstSpam
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->isMethod('POST') && ! $request->isMethod('PUT') && ! $request->isMethod('PATCH')) {
            return $next($request);
        }

        try {
            app(SpamProtection::class)->check($request->all());

            return $next($request);
        } catch (SpamException) {
            event(new SpamDetectedEvent($request));

            return $this->spamResponder->respond($request, $next);
        }
    }
}
