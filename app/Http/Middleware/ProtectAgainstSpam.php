<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Honeypot\Events\SpamDetectedEvent;
use Spatie\Honeypot\Exceptions\SpamException;
use Spatie\Honeypot\ProtectAgainstSpam as SpatieProtectAgainstSpam;
use Spatie\Honeypot\SpamProtection;
use Symfony\Component\HttpFoundation\Response;

class ProtectAgainstSpam extends SpatieProtectAgainstSpam
{
    /**
     * Handle an incoming request.
     *
     * The method is checked with isMethod() rather than getRealMethod() on
     * purpose: isMethod() honours Laravel's _method spoofing, so a form
     * posted as POST with _method=PUT is still protected. getRealMethod()
     * would report POST and let the spoofed PUT slip through unchecked.
     *
     * The response is always coerced into a Symfony Response. A middleware
     * that returns null (or a non-Response) leaves the kernel with nothing
     * valid to send, and the browser reports that as ERR_INVALID_RESPONSE.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('POST') && ! $request->isMethod('PUT') && ! $request->isMethod('PATCH')) {
            return $this->toResponse($next($request));
        }

        try {
            app(SpamProtection::class)->check($request->all());
        } catch (SpamException) {
            event(new SpamDetectedEvent($request));

            return $this->toResponse($this->spamResponder->respond($request, $next));
        }

        $response = $next($request);

        return $this->toResponse($response);
    }

    /**
     * Guarantee the middleware chain always yields a valid Response object.
     */
    protected function toResponse(mixed $response): Response
    {
        if ($response instanceof Response) {
            return $response;
        }

        if ($response === null || is_scalar($response)) {
            return new Response((string) $response);
        }

        return new Response('');
    }
}
