<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Contracts;

interface McpToolInterface
{
    public function name(): string;

    public function description(): string;

    /**
     * JSON Schema object for tool arguments.
     *
     * @return array<string, mixed>
     */
    public function inputSchema(): array;

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: list<array{type: string, text: string}>, isError?: bool}
     */
    public function handle(array $arguments): array;
}
