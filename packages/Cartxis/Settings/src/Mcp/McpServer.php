<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Throwable;

class McpServer
{
    public const PROTOCOL_VERSION = '2024-11-05';

    public function __construct(
        protected McpToolRegistry $registry
    ) {}

    public function handle(Request $request): JsonResponse|Response
    {
        $payload = $request->json()->all();

        if ($payload === [] && is_string($request->getContent()) && $request->getContent() !== '') {
            $decoded = json_decode($request->getContent(), true);
            $payload = is_array($decoded) ? $decoded : [];
        }

        // Batch requests
        if ($this->isList($payload) && array_is_list($payload)) {
            $results = [];
            foreach ($payload as $message) {
                if (! is_array($message)) {
                    continue;
                }
                $response = $this->handleMessage($message);
                if ($response !== null) {
                    $results[] = $response;
                }
            }

            return response()->json($results);
        }

        if (! is_array($payload) || $payload === []) {
            return $this->jsonRpcError(null, -32700, 'Parse error', 400);
        }

        $result = $this->handleMessage($payload);

        // Notifications have no response body
        if ($result === null) {
            return response()->noContent();
        }

        return response()->json($result);
    }

    /**
     * @param  array<string, mixed>  $message
     * @return array<string, mixed>|null
     */
    protected function handleMessage(array $message): ?array
    {
        $id = $message['id'] ?? null;
        $method = $message['method'] ?? null;
        $params = is_array($message['params'] ?? null) ? $message['params'] : [];

        if (! is_string($method) || $method === '') {
            return $this->errorPayload($id, -32600, 'Invalid Request');
        }

        // Notifications (no id) — acknowledge silently
        $isNotification = ! array_key_exists('id', $message);

        try {
            $result = match ($method) {
                'initialize' => $this->initialize($params),
                'notifications/initialized', 'initialized' => null,
                'ping' => (object) [],
                'tools/list' => ['tools' => $this->registry->listDefinitions()],
                'tools/call' => $this->callTool($params),
                default => throw new InvalidArgumentException("Method not found: {$method}"),
            };
        } catch (InvalidArgumentException $e) {
            if ($isNotification) {
                return null;
            }

            $code = str_starts_with($e->getMessage(), 'Method not found') ? -32601 : -32602;

            return $this->errorPayload($id, $code, $e->getMessage());
        } catch (Throwable $e) {
            if ($isNotification) {
                return null;
            }

            return $this->errorPayload($id, -32603, 'Internal error: '.$e->getMessage());
        }

        if ($isNotification || $result === null) {
            return null;
        }

        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function initialize(array $params): array
    {
        return [
            'protocolVersion' => self::PROTOCOL_VERSION,
            'capabilities' => [
                'tools' => new \stdClass,
            ],
            'serverInfo' => [
                'name' => 'cartxis-mcp',
                'version' => '1.0.0',
            ],
            'instructions' => 'Cartxis store management MCP server. Use tools to manage catalog, orders, customers, and safe store settings.',
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{content: list<array{type: string, text: string}>, isError?: bool}
     */
    protected function callTool(array $params): array
    {
        $name = $params['name'] ?? null;
        $arguments = is_array($params['arguments'] ?? null) ? $params['arguments'] : [];

        if (! is_string($name) || $name === '') {
            throw new InvalidArgumentException('Missing tool name');
        }

        $tool = $this->registry->get($name);

        return $tool->handle($arguments);
    }

    /**
     * @return array{jsonrpc: string, id: mixed, error: array{code: int, message: string}}
     */
    protected function errorPayload(mixed $id, int $code, string $message): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];
    }

    protected function jsonRpcError(mixed $id, int $code, string $message, int $httpStatus): JsonResponse
    {
        return response()->json($this->errorPayload($id, $code, $message), $httpStatus);
    }

    protected function isList(mixed $value): bool
    {
        return is_array($value) && array_is_list($value);
    }
}
