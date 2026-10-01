<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp;

use Cartxis\Settings\Mcp\Contracts\McpToolInterface;
use Cartxis\Settings\Mcp\Tools\ActivateTemplateTool;
use Cartxis\Settings\Mcp\Tools\CreatePageTool;
use Cartxis\Settings\Mcp\Tools\CreateProductTool;
use Cartxis\Settings\Mcp\Tools\GetOrderTool;
use Cartxis\Settings\Mcp\Tools\GetPageLayoutTool;
use Cartxis\Settings\Mcp\Tools\GetPageTool;
use Cartxis\Settings\Mcp\Tools\GetProductTool;
use Cartxis\Settings\Mcp\Tools\GetSettingsTool;
use Cartxis\Settings\Mcp\Tools\GetTemplateTool;
use Cartxis\Settings\Mcp\Tools\ListCustomersTool;
use Cartxis\Settings\Mcp\Tools\ListEditorBlocksTool;
use Cartxis\Settings\Mcp\Tools\ListOrdersTool;
use Cartxis\Settings\Mcp\Tools\ListPagesTool;
use Cartxis\Settings\Mcp\Tools\ListProductsTool;
use Cartxis\Settings\Mcp\Tools\ListTemplatesTool;
use Cartxis\Settings\Mcp\Tools\PublishPageLayoutTool;
use Cartxis\Settings\Mcp\Tools\SearchTool;
use Cartxis\Settings\Mcp\Tools\StoreInfoTool;
use Cartxis\Settings\Mcp\Tools\UpdateOrderStatusTool;
use Cartxis\Settings\Mcp\Tools\UpdatePageLayoutTool;
use Cartxis\Settings\Mcp\Tools\UpdatePageTool;
use Cartxis\Settings\Mcp\Tools\UpdateProductTool;
use Cartxis\Settings\Mcp\Tools\UpdateSettingsTool;
use InvalidArgumentException;

class McpToolRegistry
{
    /** @var array<string, McpToolInterface>|null */
    private ?array $tools = null;

    /**
     * @return list<class-string<McpToolInterface>>
     */
    public function toolClasses(): array
    {
        return [
            // Store / catalog / commerce
            StoreInfoTool::class,
            ListProductsTool::class,
            GetProductTool::class,
            CreateProductTool::class,
            UpdateProductTool::class,
            ListOrdersTool::class,
            GetOrderTool::class,
            UpdateOrderStatusTool::class,
            GetSettingsTool::class,
            UpdateSettingsTool::class,
            ListCustomersTool::class,
            SearchTool::class,

            // Storefront templates (themes)
            ListTemplatesTool::class,
            GetTemplateTool::class,
            ActivateTemplateTool::class,

            // CMS pages + Cartxis UI / template editor
            ListPagesTool::class,
            GetPageTool::class,
            CreatePageTool::class,
            UpdatePageTool::class,
            GetPageLayoutTool::class,
            UpdatePageLayoutTool::class,
            PublishPageLayoutTool::class,
            ListEditorBlocksTool::class,
        ];
    }

    /**
     * @return array<string, McpToolInterface>
     */
    public function all(): array
    {
        if ($this->tools === null) {
            $this->tools = [];
            foreach ($this->toolClasses() as $class) {
                /** @var McpToolInterface $tool */
                $tool = app($class);
                $this->tools[$tool->name()] = $tool;
            }
        }

        return $this->tools;
    }

    public function get(string $name): McpToolInterface
    {
        $tools = $this->all();

        if (! isset($tools[$name])) {
            throw new InvalidArgumentException("Unknown MCP tool: {$name}");
        }

        return $tools[$name];
    }

    /**
     * @return list<array{name: string, description: string, inputSchema: array<string, mixed>}>
     */
    public function listDefinitions(): array
    {
        return array_values(array_map(function (McpToolInterface $tool) {
            return [
                'name' => $tool->name(),
                'description' => $tool->description(),
                'inputSchema' => $tool->inputSchema(),
            ];
        }, $this->all()));
    }
}
