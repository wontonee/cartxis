<?php

declare(strict_types=1);

namespace Vortex\System\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Vortex\System\Services\MaintenanceService;

class DisableScheduledMaintenance implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(MaintenanceService $maintenanceService): void
    {
        $maintenanceService->disable();
    }
}
