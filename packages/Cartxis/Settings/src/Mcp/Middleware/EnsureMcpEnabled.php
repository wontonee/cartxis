<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Middleware;

use Cartxis\Core\Services\SettingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMcpEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = (bool) app(SettingService::class)->get('mcp.enabled', false);

        if (! $enabled) {
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $request->json('id'),
                'error' => [
                    'code' => -32003,
                    'message' => 'MCP is disabled. Enable it in Admin → Settings → MCP.',
                ],
            ], 503);
        }

        return $next($request);
    }
}
