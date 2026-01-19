<?php

declare(strict_types=1);

namespace Cartxis\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Cartxis\System\Services\CacheService;

class CacheController extends Controller
{
    public function __construct(
        protected CacheService $cacheService
    ) {}
    
    /**
     * Display cache management page
     */
    public function index(): Response
    {
        return Inertia::render('Admin/System/Cache/Index', [
            'statistics' => $this->cacheService->getStatistics(),
        ]);
    }
    
    /**
     * Get cache statistics
     */
    public function statistics(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->cacheService->getStatistics(),
        ]);
    }
    
    /**
     * Clear specified cache types
     */
    public function clear(Request $request)
    {
        $request->validate([
            'types' => 'required|array',
            'types.*' => 'in:application,config,route,view,event',
        ]);
        
        $start = microtime(true);
        $cleared = $this->cacheService->clearCache($request->types);
        $duration = round(microtime(true) - $start, 2);
        
        return redirect()->back()->with('success', 
            'Cleared ' . count($cleared) . ' cache type(s) in ' . $duration . 's'
        );
    }
    
    /**
     * Rebuild specified cache types
     */
    public function rebuild(Request $request)
    {
        $request->validate([
            'types' => 'required|array',
            'types.*' => 'in:config,route,event',
        ]);
        
        $start = microtime(true);
        $rebuilt = $this->cacheService->rebuildCache($request->types);
        $duration = round(microtime(true) - $start, 2);
        
        return redirect()->back()->with('success',
            'Rebuilt ' . count($rebuilt) . ' cache type(s) in ' . $duration . 's'
        );
    }
}
