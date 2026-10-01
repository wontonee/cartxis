<?php

declare(strict_types=1);

use App\Models\User;
use Cartxis\Core\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function mcpAdmin(): User
{
    return User::factory()->withoutTwoFactor()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);
}

function mcpToken(User $admin): string
{
    return $admin->createToken('test-mcp', ['mcp'])->plainTextToken;
}

function enableMcp(bool $enabled = true): void
{
    app(SettingService::class)->set('mcp.enabled', $enabled, 'boolean', 'mcp');
}

test('mcp rejects unauthenticated requests', function () {
    enableMcp();

    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [],
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('error.message', fn ($message) => is_string($message) && $message !== '');
});

test('mcp rejects when disabled', function () {
    enableMcp(false);
    $admin = mcpAdmin();
    $token = mcpToken($admin);

    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(503)
        ->assertJsonPath('error.code', -32003);
});

test('mcp returns 503 when disabled even without auth', function () {
    enableMcp(false);

    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [],
    ]);

    $response->assertStatus(503)
        ->assertJsonPath('error.code', -32003);
});

test('mcp update settings refuses secret keys', function () {
    enableMcp();
    $admin = mcpAdmin();
    $token = mcpToken($admin);

    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 10,
        'method' => 'tools/call',
        'params' => [
            'name' => 'cartxis_update_settings',
            'arguments' => [
                'key' => 'ai.providers',
                'value' => ['secret' => true],
            ],
        ],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonPath('result.isError', true);

    $text = $response->json('result.content.0.text');
    $payload = json_decode($text, true);
    expect($payload['error'] ?? '')->toContain('Key not allowed');
});

test('mcp can create and list a product', function () {
    enableMcp();
    $admin = mcpAdmin();
    $token = mcpToken($admin);

    $create = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 11,
        'method' => 'tools/call',
        'params' => [
            'name' => 'cartxis_create_product',
            'arguments' => [
                'name' => 'MCP Test Product',
                'sku' => 'MCP-TEST-001',
                'price' => 19.99,
                'status' => 'enabled',
            ],
        ],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $create->assertOk()
        ->assertJsonPath('result.content.0.type', 'text');

    $created = json_decode($create->json('result.content.0.text'), true);
    expect($created['product']['sku'] ?? null)->toBe('MCP-TEST-001');

    $list = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 12,
        'method' => 'tools/call',
        'params' => [
            'name' => 'cartxis_list_products',
            'arguments' => [
                'search' => 'MCP-TEST-001',
            ],
        ],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $list->assertOk();
    $listed = json_decode($list->json('result.content.0.text'), true);
    expect($listed['data'] ?? [])->not->toBeEmpty();
});

test('mcp initialize and tools list succeed with valid token', function () {
    enableMcp();
    $admin = mcpAdmin();
    $token = mcpToken($admin);

    $init = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [],
            'clientInfo' => ['name' => 'pest', 'version' => '1.0'],
        ],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $init->assertOk()
        ->assertJsonPath('result.serverInfo.name', 'cartxis-mcp')
        ->assertJsonPath('result.protocolVersion', '2024-11-05');

    $list = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/list',
        'params' => [],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $list->assertOk();
    $tools = $list->json('result.tools');
    expect($tools)->toBeArray()->and(count($tools))->toBeGreaterThanOrEqual(20);

    $names = collect($tools)->pluck('name')->all();
    expect($names)->toContain('cartxis_store_info')
        ->and($names)->toContain('cartxis_list_templates')
        ->and($names)->toContain('cartxis_create_page')
        ->and($names)->toContain('cartxis_update_page_layout')
        ->and($names)->toContain('cartxis_list_editor_blocks');
});

test('mcp tools call cartxis_store_info works', function () {
    enableMcp();
    app(SettingService::class)->set('store_name', 'Pest Store', 'string', 'store');
    app(SettingService::class)->set('site_name', 'Pest Site', 'string', 'general');

    $admin = mcpAdmin();
    $token = mcpToken($admin);

    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => [
            'name' => 'cartxis_store_info',
            'arguments' => [],
        ],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonPath('result.content.0.type', 'text');

    $text = $response->json('result.content.0.text');
    $payload = json_decode($text, true);

    expect($payload)->toBeArray()
        ->and($payload['store_name'])->toBe('Pest Store')
        ->and($payload['site_name'])->toBe('Pest Site');
});

test('admin can create and revoke mcp tokens via settings routes', function () {
    $admin = mcpAdmin();

    $this->actingAs($admin, 'admin');

    $create = $this->post(route('admin.settings.mcp.tokens.store'), [
        'name' => 'Cursor MCP',
    ]);

    $create->assertRedirect(route('admin.settings.mcp.index'));
    $create->assertSessionHas('mcp_plain_text_token');

    $tokenId = $admin->tokens()->first()?->id;
    expect($tokenId)->not->toBeNull();
    expect($admin->tokens()->first()->can('mcp'))->toBeTrue();

    $revoke = $this->delete(route('admin.settings.mcp.tokens.revoke', $tokenId));
    $revoke->assertRedirect(route('admin.settings.mcp.index'));

    expect($admin->fresh()->tokens()->count())->toBe(0);
});

test('admin can toggle mcp enabled setting', function () {
    $admin = mcpAdmin();
    $this->actingAs($admin, 'admin');

    $response = $this->post(route('admin.settings.mcp.save'), [
        'mcp_enabled' => true,
    ]);

    $response->assertRedirect(route('admin.settings.mcp.index'));
    expect((bool) app(SettingService::class)->get('mcp.enabled', false))->toBeTrue();
});

test('non-mcp sanctum token is rejected', function () {
    enableMcp();
    $admin = mcpAdmin();
    $token = $admin->createToken('api-only', ['api'])->plainTextToken;

    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'ping',
        'params' => [],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertUnauthorized();
});

test('mcp can create page and save ui editor layout', function () {
    enableMcp();
    $admin = mcpAdmin();
    $token = mcpToken($admin);

    $create = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 20,
        'method' => 'tools/call',
        'params' => [
            'name' => 'cartxis_create_page',
            'arguments' => [
                'title' => 'MCP Landing',
                'url_key' => 'mcp-landing',
                'status' => 'draft',
            ],
        ],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $create->assertOk();
    $created = json_decode($create->json('result.content.0.text'), true);
    $pageId = $created['page']['id'] ?? null;
    expect($pageId)->not->toBeNull();

    $blocks = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 21,
        'method' => 'tools/call',
        'params' => [
            'name' => 'cartxis_list_editor_blocks',
            'arguments' => [],
        ],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $blocks->assertOk();
    $blockPayload = json_decode($blocks->json('result.content.0.text'), true);
    expect($blockPayload['count'] ?? 0)->toBeGreaterThan(0);

    $save = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 22,
        'method' => 'tools/call',
        'params' => [
            'name' => 'cartxis_update_page_layout',
            'arguments' => [
                'page_id' => $pageId,
                'publish' => true,
                'layout_data' => [
                    'version' => '2.0',
                    'sections' => [
                        [
                            'id' => 'sec-1',
                            'columns' => [
                                [
                                    'id' => 'col-1',
                                    'blocks' => [
                                        [
                                            'id' => 'blk-1',
                                            'type' => 'text',
                                            'settings' => [
                                                'content' => '<p>Hello from MCP</p>',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $save->assertOk();
    $saved = json_decode($save->json('result.content.0.text'), true);
    expect($saved['layout']['status'] ?? null)->toBe('published')
        ->and($saved['layout']['section_count'] ?? 0)->toBe(1);

    $get = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 23,
        'method' => 'tools/call',
        'params' => [
            'name' => 'cartxis_get_page_layout',
            'arguments' => ['page_id' => $pageId],
        ],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $get->assertOk();
    $layout = json_decode($get->json('result.content.0.text'), true);
    expect($layout['layout_data']['sections'] ?? [])->toHaveCount(1);
});

test('mcp can list templates', function () {
    enableMcp();
    $admin = mcpAdmin();
    $token = mcpToken($admin);

    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 30,
        'method' => 'tools/call',
        'params' => [
            'name' => 'cartxis_list_templates',
            'arguments' => [],
        ],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonPath('result.content.0.type', 'text');

    $payload = json_decode($response->json('result.content.0.text'), true);
    expect($payload)->toHaveKey('data')
        ->and($payload)->toHaveKey('count');
});
