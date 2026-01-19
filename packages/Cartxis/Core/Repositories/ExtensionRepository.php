<?php

namespace Cartxis\Core\Repositories;

use Cartxis\Core\Models\Extension;
use Illuminate\Support\Collection;

class ExtensionRepository
{
    /**
     * Find extension by ID.
     */
    public function find(int $id): ?Extension
    {
        return Extension::find($id);
    }

    /**
     * Find extension by code.
     */
    public function findByCode(string $code): ?Extension
    {
        return Extension::where('code', $code)->first();
    }

    /**
     * Get all extensions.
     */
    public function all(): Collection
    {
        return Extension::all();
    }

    /**
     * Get installed extensions.
     */
    public function getInstalled(): Collection
    {
        return Extension::installed()->get();
    }

    /**
     * Get active extensions.
     */
    public function getActive(): Collection
    {
        return Extension::active()->get();
    }

    /**
     * Create a new extension.
     */
    public function create(array $data): Extension
    {
        return Extension::create($data);
    }

    /**
     * Update an extension.
     */
    public function update(int $id, array $data): bool
    {
        return Extension::where('id', $id)->update($data) > 0;
    }

    /**
     * Delete an extension.
     */
    public function delete(int $id): bool
    {
        return Extension::where('id', $id)->delete() > 0;
    }

    /**
     * Mark extension as installed.
     */
    public function markAsInstalled(string $code): bool
    {
        return Extension::where('code', $code)->update([
            'installed' => true,
            'installed_at' => now(),
        ]) > 0;
    }

    /**
     * Mark extension as uninstalled.
     */
    public function markAsUninstalled(string $code): bool
    {
        return Extension::where('code', $code)->update([
            'installed' => false,
            'active' => false,
            'installed_at' => null,
        ]) > 0;
    }

    /**
     * Activate extension.
     */
    public function activate(string $code): bool
    {
        return Extension::where('code', $code)->update(['active' => true]) > 0;
    }

    /**
     * Deactivate extension.
     */
    public function deactivate(string $code): bool
    {
        return Extension::where('code', $code)->update(['active' => false]) > 0;
    }

    /**
     * Update extension version.
     */
    public function updateVersion(string $code, string $version): bool
    {
        return Extension::where('code', $code)->update(['version' => $version]) > 0;
    }

    /**
     * Update extension configuration.
     */
    public function updateConfig(string $code, array $config): bool
    {
        $extension = $this->findByCode($code);
        
        if (!$extension) {
            return false;
        }

        $extension->config = array_merge($extension->config ?? [], $config);
        return $extension->save();
    }

    /**
     * Get extension configuration.
     */
    public function getConfig(string $code): ?array
    {
        $extension = $this->findByCode($code);
        return $extension?->config;
    }
}
