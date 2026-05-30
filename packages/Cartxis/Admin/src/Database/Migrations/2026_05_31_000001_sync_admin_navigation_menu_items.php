<?php

declare(strict_types=1);

use Cartxis\Admin\Services\AdminMenuSyncService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(AdminMenuSyncService::class)->sync();
    }

    public function down(): void
    {
        // Menu sync is additive/corrective; no safe automatic rollback.
    }
};
