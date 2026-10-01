<?php

declare(strict_types=1);

namespace Cartxis\Settings\Http\Controllers;

use Cartxis\Settings\Mcp\McpServer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class McpController
{
    public function __construct(
        protected McpServer $server
    ) {}

    public function handle(Request $request): JsonResponse|Response
    {
        return $this->server->handle($request);
    }

    /**
     * Optional GET for clients that probe the endpoint.
     * Auth + mcp.enabled are enforced by route middleware.
     */
    public function info(Request $request): JsonResponse
    {
        return response()->json([
            'name' => 'cartxis-mcp',
            'version' => '1.0.0',
            'protocolVersion' => McpServer::PROTOCOL_VERSION,
            'transport' => 'streamable-http',
            'endpoint' => url('/mcp'),
        ]);
    }
}
