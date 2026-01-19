<?php

namespace Cartxis\Settings\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Cartxis\Core\Models\Channel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChannelsController extends Controller
{
    /**
     * Display a listing of channels
     */
    public function index()
    {
        // Get all channels with their themes
        $channels = Channel::with('theme')
            ->ordered()
            ->paginate(10);

        // Get available themes (assuming they exist)
        $availableThemes = \DB::table('themes')
            ->where('is_active', true)
            ->select('id', 'name', 'slug', 'description')
            ->get();

        return Inertia::render('Admin/Settings/Channels/Index', [
            'channels' => $channels,
            'availableThemes' => $availableThemes,
        ]);
    }

    /**
     * Show the form for creating a new channel
     */
    public function create()
    {
        $availableThemes = \DB::table('themes')
            ->where('is_active', true)
            ->select('id', 'name', 'slug', 'description')
            ->get();

        return Inertia::render('Admin/Settings/Channels/Create', [
            'availableThemes' => $availableThemes,
        ]);
    }

    /**
     * Store a newly created channel in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:channels,name|max:255',
            'slug' => 'required|string|unique:channels,slug|max:255',
            'theme_id' => 'required|exists:themes,id',
            'status' => 'required|in:active,inactive',
            'url' => 'nullable|url|max:255',
            'description' => 'nullable|string',
        ]);

        $channel = Channel::create($validated);

        return redirect()
            ->route('admin.settings.channels.index')
            ->with('success', 'Channel created successfully');
    }

    /**
     * Display the specified channel
     */
    public function show(Channel $channel)
    {
        return Inertia::render('Admin/Settings/Channels/Show', [
            'channel' => $channel->load('theme'),
        ]);
    }

    /**
     * Show the form for editing the specified channel
     */
    public function edit(Channel $channel)
    {
        $availableThemes = \DB::table('themes')
            ->where('is_active', true)
            ->select('id', 'name', 'slug', 'description')
            ->get();

        return Inertia::render('Admin/Settings/Channels/Edit', [
            'channel' => $channel->load('theme'),
            'availableThemes' => $availableThemes,
        ]);
    }

    /**
     * Update the specified channel in storage
     */
    public function update(Request $request, Channel $channel)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:channels,name,' . $channel->id . '|max:255',
            'slug' => 'required|string|unique:channels,slug,' . $channel->id . '|max:255',
            'theme_id' => 'required|exists:themes,id',
            'status' => 'required|in:active,inactive',
            'url' => 'nullable|url|max:255',
            'description' => 'nullable|string',
        ]);

        $channel->update($validated);

        return redirect()
            ->route('admin.settings.channels.index')
            ->with('success', 'Channel updated successfully');
    }

    /**
     * Remove the specified channel from storage
     */
    public function destroy(Channel $channel)
    {
        // Prevent deletion of default channel
        if ($channel->is_default) {
            return back()->with('error', 'Cannot delete the default channel');
        }

        $channel->delete();

        return redirect()
            ->route('admin.settings.channels.index')
            ->with('success', 'Channel deleted successfully');
    }

    /**
     * Update the theme for a channel
     */
    public function updateTheme(Request $request, Channel $channel)
    {
        $validated = $request->validate([
            'theme_id' => 'required|exists:themes,id',
        ]);

        $channel->update($validated);

        // Dispatch event for theme change
        event(new \App\Events\ChannelThemeChanged($channel));

        return response()->json([
            'success' => true,
            'message' => 'Theme updated successfully',
            'data' => [
                'channel_id' => $channel->id,
                'theme_id' => $channel->theme_id,
                'theme_name' => $channel->theme->name ?? null,
                'updated_at' => $channel->updated_at,
            ],
        ]);
    }

    /**
     * Set a channel as the default
     */
    public function setDefault(Request $request, Channel $channel)
    {
        $previousDefault = Channel::where('is_default', true)->first();

        $channel->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => 'Default channel updated',
            'data' => [
                'new_default_id' => $channel->id,
                'previous_default_id' => $previousDefault->id ?? null,
                'updated_at' => $channel->updated_at,
            ],
        ]);
    }

    /**
     * Toggle channel status
     */
    public function toggleStatus(Request $request, Channel $channel)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $channel->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Channel status updated',
            'data' => [
                'channel_id' => $channel->id,
                'status' => $channel->status,
                'updated_at' => $channel->updated_at,
            ],
        ]);
    }
}
