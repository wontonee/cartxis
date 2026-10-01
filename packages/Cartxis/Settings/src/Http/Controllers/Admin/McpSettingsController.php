<?php

declare(strict_types=1);

namespace Cartxis\Settings\Http\Controllers\Admin;

use App\Models\User;
use Carbon\Carbon;
use Cartxis\Core\Services\SettingService;
use Cartxis\Settings\Mcp\McpToolRegistry;
use Cartxis\Settings\Mcp\Support\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Sanctum\PersonalAccessToken;

class McpSettingsController
{
    public function __construct(
        protected SettingService $settingService,
        protected McpToolRegistry $toolRegistry
    ) {}

    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::guard('admin')->user();

        return Inertia::render('Admin/Settings/MCP/Index', [
            'endpointUrl' => url('/mcp'),
            'localCaCertPath' => $this->localCaCertPath(),
            'settings' => [
                'mcp_enabled' => (bool) $this->settingService->get('mcp.enabled', false),
            ],
            'tokens' => $this->mcpTokensFor($user),
            'tools' => collect($this->toolRegistry->listDefinitions())->map(fn (array $tool) => [
                'name' => $tool['name'],
                'description' => $tool['description'],
            ])->values()->all(),
            'plainTextToken' => session('mcp_plain_text_token'),
        ]);
    }

    /**
     * Absolute path to Herd/Valet self-signed CA when present (local HTTPS MCP clients).
     */
    private function localCaCertPath(): ?string
    {
        $home = $_SERVER['HOME'] ?? getenv('HOME') ?: null;
        if (! is_string($home) || $home === '') {
            return null;
        }

        $candidates = [
            $home.'/Library/Application Support/Herd/config/valet/CA/LaravelValetCASelfSigned.pem',
            $home.'/.config/herd/config/valet/CA/LaravelValetCASelfSigned.pem',
            $home.'/.composer/vendor/laravel/valet/CA/LaravelValetCASelfSigned.pem',
        ];

        foreach ($candidates as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        return $candidates[0];
    }

    public function save(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'mcp_enabled' => 'nullable|boolean',
            ]);

            $this->settingService->set(
                'mcp.enabled',
                (bool) ($validated['mcp_enabled'] ?? false),
                'boolean',
                'mcp'
            );

            return redirect()
                ->route('admin.settings.mcp.index')
                ->with('success', 'MCP settings saved successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('MCP settings save error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to save MCP settings. Please try again.');
        }
    }

    public function createToken(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::guard('admin')->user();

        if (! AdminUser::isAdmin($user)) {
            return redirect()->back()->with('error', 'Only administrators can create MCP tokens.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $expiresAt = isset($validated['expires_at'])
            ? Carbon::parse($validated['expires_at'])
            : null;

        $newToken = $user->createToken(
            $validated['name'],
            ['mcp'],
            $expiresAt
        );

        return redirect()
            ->route('admin.settings.mcp.index')
            ->with('success', 'MCP token created. Copy it now — it will not be shown again.')
            ->with('mcp_plain_text_token', $newToken->plainTextToken);
    }

    public function revokeToken(Request $request, int $tokenId): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::guard('admin')->user();

        if (! AdminUser::isAdmin($user)) {
            return redirect()->back()->with('error', 'Only administrators can revoke MCP tokens.');
        }

        $token = PersonalAccessToken::query()
            ->where('id', $tokenId)
            ->where('tokenable_type', $user->getMorphClass())
            ->where('tokenable_id', $user->id)
            ->first();

        if (! $token || ! $token->can('mcp')) {
            return redirect()->back()->with('error', 'MCP token not found.');
        }

        $token->delete();

        return redirect()
            ->route('admin.settings.mcp.index')
            ->with('success', 'MCP token revoked.');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mcpTokensFor(User $user): array
    {
        return $user->tokens()
            ->orderByDesc('id')
            ->get()
            ->filter(fn (PersonalAccessToken $token) => $token->can('mcp'))
            ->map(fn (PersonalAccessToken $token) => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => optional($token->last_used_at)?->toIso8601String(),
                'expires_at' => optional($token->expires_at)?->toIso8601String(),
                'created_at' => optional($token->created_at)?->toIso8601String(),
            ])
            ->values()
            ->all();
    }
}
