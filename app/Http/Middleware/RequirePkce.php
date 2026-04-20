<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePkce
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('oauth/authorize') || ! $request->isMethod('GET')) {
            return $next($request);
        }

        $challenge = $request->query('code_challenge');

        if (empty($challenge)) {
            return $this->error('code_challenge is required — PKCE is mandatory for all clients');
        }

        $method = $request->query('code_challenge_method', 'plain');

        if ($method !== 'S256') {
            return $this->error('code_challenge_method must be "S256"');
        }

        return $next($request);
    }

    private function error(string $description): Response
    {
        return response()->json([
            'error' => 'invalid_request',
            'error_description' => $description,
        ], 400);
    }
}
