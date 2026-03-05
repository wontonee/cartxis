<?php

declare(strict_types=1);

namespace Cartxis\UIEditor\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Cartxis\UIEditor\Models\SavedBlock;

class SavedBlocksController extends Controller
{
    /**
     * GET /admin/uieditor/snippets
     * Return built-in (seeded) snippets.
     */
    public function snippets(): JsonResponse
    {
        $snippets = SavedBlock::where('is_builtin', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'type', 'layout_data', 'is_builtin', 'created_at']);

        return response()->json($snippets);
    }

    /**
     * GET /admin/uieditor/saved-blocks
     * Return user-created saved blocks (not built-in).
     */
    public function index(): JsonResponse
    {
        $blocks = SavedBlock::where('is_builtin', false)
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'type', 'layout_data', 'is_builtin', 'created_at']);

        return response()->json($blocks);
    }

    /**
     * POST /admin/uieditor/saved-blocks
     * Save a new reusable section/block.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'type'        => ['required', 'in:section,block'],
            'layout_data' => ['required', 'array'],
        ]);

        $saved = SavedBlock::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'type'        => $data['type'],
            'layout_data' => $data['layout_data'],
            'created_by'  => auth()->id(),
        ]);

        return response()->json([
            'id'          => $saved->id,
            'name'        => $saved->name,
            'description' => $saved->description,
            'type'        => $saved->type,
            'layout_data' => $saved->layout_data,
            'created_at'  => $saved->created_at,
        ], 201);
    }

    /**
     * DELETE /admin/uieditor/saved-blocks/{id}
     * Remove a saved block.
     */
    public function destroy(int $id): JsonResponse
    {
        $block = SavedBlock::findOrFail($id);
        $block->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
