<?php

declare(strict_types=1);

namespace Vortex\CMS\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\CMS\Http\Requests\StoreBlockRequest;
use Vortex\CMS\Http\Requests\UpdateBlockRequest;
use Vortex\CMS\Http\Resources\BlockResource;
use Vortex\CMS\Models\Block;
use Vortex\CMS\Repositories\BlockRepository;
use Vortex\CMS\Services\BlockService;

class BlocksController extends Controller
{
    public function __construct(
        protected BlockRepository $blockRepository,
        protected BlockService $blockService
    ) {}

    /**
     * Display a listing of blocks.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'type', 'sort_by', 'sort_order']);
        $perPage = (int) $request->input('per_page', 15);

        $blocks = $this->blockRepository->paginate($filters, $perPage);
        $statistics = $this->blockRepository->getCountByStatus();

        return Inertia::render('Admin/Content/Blocks/Index', [
            'blocks' => $blocks,
            'statistics' => $statistics,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new block.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Content/Blocks/Create');
    }

    /**
     * Store a newly created block.
     */
    public function store(StoreBlockRequest $request): RedirectResponse
    {
        $this->blockService->create($request->validated());

        return redirect()
            ->route('admin.content.blocks.index')
            ->with('success', 'Block created successfully.');
    }

    /**
     * Display the specified block.
     */
    public function show(Block $block): Response
    {
        return Inertia::render('Admin/Content/Blocks/Show', [
            'block' => new BlockResource($block->load(['creator', 'updater'])),
        ]);
    }

    /**
     * Show the form for editing the specified block.
     */
    public function edit(Block $block): Response
    {
        return Inertia::render('Admin/Content/Blocks/Edit', [
            'block' => new BlockResource($block->load(['creator', 'updater'])),
        ]);
    }

    /**
     * Update the specified block.
     */
    public function update(UpdateBlockRequest $request, Block $block): RedirectResponse
    {
        $this->blockService->update($block, $request->validated());

        return redirect()
            ->route('admin.content.blocks.index')
            ->with('success', 'Block updated successfully.');
    }

    /**
     * Remove the specified block.
     */
    public function destroy(Block $block): RedirectResponse
    {
        $block->delete();

        return redirect()
            ->route('admin.content.blocks.index')
            ->with('success', 'Block deleted successfully.');
    }

    /**
     * Perform bulk actions on blocks.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:blocks,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');
        $count = 0;

        switch ($action) {
            case 'activate':
                $count = $this->blockService->bulkUpdateStatus($ids, 'active');
                $message = "{$count} block(s) activated successfully.";
                break;

            case 'deactivate':
                $count = $this->blockService->bulkUpdateStatus($ids, 'inactive');
                $message = "{$count} block(s) deactivated successfully.";
                break;

            case 'delete':
                $count = $this->blockService->bulkDelete($ids);
                $message = "{$count} block(s) deleted successfully.";
                break;

            default:
                return back()->with('error', 'Invalid action.');
        }

        return back()->with('success', $message);
    }

    /**
     * Check if an identifier is available.
     */
    public function checkIdentifier(Request $request): JsonResponse
    {
        $identifier = $request->input('identifier');
        $excludeId = $request->input('exclude_id');

        $exists = $this->blockService->identifierExists($identifier, $excludeId);

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'This identifier is already taken.' : 'This identifier is available.',
        ]);
    }

    /**
     * Generate a unique identifier from title.
     */
    public function generateIdentifier(Request $request): JsonResponse
    {
        $title = $request->input('title');
        $excludeId = $request->input('exclude_id');

        $identifier = $this->blockService->generateUniqueIdentifier($title, null, $excludeId);

        return response()->json([
            'identifier' => $identifier,
        ]);
    }
}
