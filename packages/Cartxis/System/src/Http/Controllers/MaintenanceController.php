<?php

declare(strict_types=1);

namespace Cartxis\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\System\Http\Requests\EnableMaintenanceRequest;
use Cartxis\System\Http\Requests\ScheduleMaintenanceRequest;
use Cartxis\System\Http\Requests\UpdateMaintenanceSettingsRequest;
use Cartxis\System\Services\MaintenanceService;

class MaintenanceController extends Controller
{
    public function __construct(
        protected MaintenanceService $maintenanceService
    ) {}
    
    /**
     * Display maintenance management page
     */
    public function index(): Response
    {
        return Inertia::render('Admin/System/Maintenance/Index', [
            'settings' => $this->maintenanceService->getStatus(),
            'logs' => $this->maintenanceService->getHistory(10),
        ]);
    }
    
    /**
     * Get maintenance status
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->maintenanceService->getStatus(),
        ]);
    }
    
    /**
     * Enable maintenance mode
     */
    public function enable(EnableMaintenanceRequest $request)
    {
        $result = $this->maintenanceService->enable($request->validated());
        
        return back()->with([
            'success' => 'Maintenance mode enabled successfully',
            'bypass_url' => $result['bypass_url'],
            'secret' => $result['secret'],
        ]);
    }
    
    /**
     * Disable maintenance mode
     */
    public function disable()
    {
        $this->maintenanceService->disable();
        
        return back()->with('success', 'Maintenance mode disabled successfully');
    }
    
    /**
     * Schedule maintenance
     */
    public function schedule(ScheduleMaintenanceRequest $request)
    {
        $this->maintenanceService->schedule($request->validated());
        
        return back()->with('success', 'Maintenance scheduled successfully');
    }
    
    /**
     * Update maintenance settings
     */
    public function updateSettings(UpdateMaintenanceSettingsRequest $request): JsonResponse
    {
        $this->maintenanceService->updateSettings($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }
    
    /**
     * Regenerate secret token
     */
    public function regenerateSecret(): JsonResponse
    {
        $secret = $this->maintenanceService->regenerateSecret();
        
        return response()->json([
            'success' => true,
            'message' => 'Secret token regenerated successfully',
            'secret' => $secret,
            'bypass_url' => url($secret),
        ]);
    }
    
    /**
     * Get maintenance history
     */
    public function history(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->maintenanceService->getHistory(20),
        ]);
    }
}
