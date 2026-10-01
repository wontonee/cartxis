<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Middleware;

use Cartxis\Settings\Mcp\Support\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMcpToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');
        if (! is_string($header) || ! preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return $this->unauthorized('Missing or invalid Authorization Bearer token');
        }

        $plainTextToken = trim($matches[1]);
        if ($plainTextToken === '') {
            return $this->unauthorized('Missing or invalid Authorization Bearer token');
        }

        $accessToken = PersonalAccessToken::findToken($plainTextToken);
        if (! $accessToken) {
            return $this->unauthorized('Invalid MCP token');
        }

        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return $this->unauthorized('MCP token has expired');
        }

        if (! $accessToken->can('mcp')) {
            return $this->unauthorized('Token lacks mcp ability');
        }

        $user = $accessToken->tokenable;
        if (! $user instanceof \App\Models\User || ! AdminUser::isAdmin($user)) {
            return $this->unauthorized('MCP token owner is not an admin');
        }

        if (property_exists($user, 'is_active') && ! $user->is_active) {
            return $this->unauthorized('MCP token owner is inactive');
        }

        $accessToken->forceFill(['last_used_at' => now()])->save();

        $request->setUserResolver(fn () => $user);
        $request->attributes->set('mcp_token', $accessToken);

        return $next($request);
    }

    private function unauthorized(string $message): Response
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'id' => null,
            'error' => [
                'code' => -32001,
                'message' => $message,
            ],
        ], 401);
    }
}
