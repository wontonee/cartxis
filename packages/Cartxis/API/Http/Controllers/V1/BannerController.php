<?php

namespace Cartxis\API\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cartxis\API\Helpers\ApiResponse;
use Cartxis\API\Http\Resources\BannerResource;
use Cartxis\CMS\Models\Block;

class BannerController extends Controller
{
    /**
     * List active banner blocks.
     *
     * Query params:
     * - mobile: boolean (when true, filters identifier prefix to `mobile-` unless identifier/identifiers provided)
     * - placement: optional string to filter by identifier prefix (e.g. `home-hero` -> `mobile-home-hero-` when mobile=true)
     * - identifier: exact identifier
     * - identifiers: comma-separated list of identifiers
     * - channel_id: optional channel filter
     * - limit: max items (default 10, max 50)
     */
    public function index(Request $request)
    {
        $limit = min((int) $request->get('limit', 10), 50);

        $query = Block::query()
            ->where('type', 'banner')
            ->active()
            ->scheduled();

        $orderedIdentifiers = null;

        $mobile = $request->boolean('mobile');

        if ($request->filled('channel_id')) {
            $query->where('channel_id', $request->get('channel_id'));
        }

        if ($request->filled('identifier')) {
            $query->where('identifier', $request->get('identifier'));
        }

        if ($request->filled('identifiers')) {
            $identifiers = array_values(array_filter(array_map('trim', explode(',', (string) $request->get('identifiers')))));
            if (!empty($identifiers)) {
                $query->whereIn('identifier', $identifiers);
                $orderedIdentifiers = $identifiers;
            }
        }

        // Convenience filtering for mobile banners by identifier prefix.
        // Only applies when caller did not explicitly request a specific identifier(s).
        if (!$request->filled('identifier') && !$request->filled('identifiers')) {
            $prefix = null;

            if ($request->filled('placement')) {
                $placement = trim((string) $request->get('placement'));
                if ($placement !== '') {
                    $prefix = ($mobile ? 'mobile-' : '') . $placement . '-';
                }
            } elseif ($mobile) {
                $prefix = 'mobile-';
            }

            if (!empty($prefix)) {
                $query->where('identifier', 'like', $prefix . '%');
            }
        }

        if (!empty($orderedIdentifiers)) {
            $caseParts = [];
            $bindings = [];
            foreach ($orderedIdentifiers as $index => $identifier) {
                $caseParts[] = 'WHEN ? THEN ' . (int) $index;
                $bindings[] = $identifier;
            }

            $query->orderByRaw(
                'CASE identifier ' . implode(' ', $caseParts) . ' ELSE 999 END',
                $bindings
            );
        }

        $banners = $query->orderByDesc('id')
            ->limit($limit)
            ->get();

        return ApiResponse::success(
            BannerResource::collection($banners),
            'Banners retrieved successfully'
        );
    }

    /**
     * Get a single banner by identifier.
     */
    public function show(Request $request, string $identifier)
    {
        $query = Block::query()
            ->where('type', 'banner')
            ->where('identifier', $identifier);

        if ($request->filled('channel_id')) {
            $query->where('channel_id', $request->get('channel_id'));
        }

        $banner = $query->first();

        if (!$banner) {
            return ApiResponse::notFound('Banner not found', 'BANNER_NOT_FOUND');
        }

        if (!$banner->isVisible()) {
            return ApiResponse::error('Banner is not available', null, 403, 'BANNER_UNAVAILABLE');
        }

        return ApiResponse::success(
            new BannerResource($banner),
            'Banner retrieved successfully'
        );
    }
}
