<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TargetTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TargetTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = TargetTemplate::query();

        // Filter by difficulty
        if ($request->has('difficulty') && $request->difficulty !== 'all') {
            $query->where('difficulty', $request->difficulty);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('template_id', 'like', "%{$search}%")
                  ->orWhere('constellation', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderBy('display_order')->orderBy('name')->paginate(20);

        return view('admin.target-templates.index', [
            'templates' => $templates,
            'filters' => $request->only(['difficulty', 'status', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.target-templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'template_id' => 'required|string|unique:target_templates,template_id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'constellation' => 'required|string|max:100',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'short_description' => 'required|string',
            'full_description' => 'nullable|string',
            'tips' => 'nullable|string',
            'preview_image' => 'nullable|image|max:5120',
            'thumbnail_image' => 'nullable|image|max:2048',
            'ra_hours' => 'required|integer|min:0|max:23',
            'ra_minutes' => 'required|integer|min:0|max:59',
            'ra_seconds' => 'required|numeric|min:0|max:59.9',
            'dec_degrees' => 'required|integer|min:-90|max:90',
            'dec_minutes' => 'required|integer|min:0|max:59',
            'dec_seconds' => 'required|numeric|min:0|max:59.9',
            'best_months' => 'required|array',
            'estimated_time' => 'nullable|string|max:50',
            'recommended_shots' => 'nullable|array',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer',
            'tags' => 'nullable|array',
        ]);

        // Handle image uploads
        if ($request->hasFile('preview_image')) {
            $validated['preview_image'] = $request->file('preview_image')->store('target-templates', 'public');
        }

        if ($request->hasFile('thumbnail_image')) {
            $validated['thumbnail_image'] = $request->file('thumbnail_image')->store('target-templates/thumbnails', 'public');
        }

        $template = TargetTemplate::create($validated);

        return redirect()->route('admin.target-templates.index')
            ->with('success', 'Template créé avec succès!');
    }

    /**
     * Display the specified resource.
     */
    public function show(TargetTemplate $targetTemplate): View
    {
        return view('admin.target-templates.show', [
            'template' => $targetTemplate,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TargetTemplate $targetTemplate): View
    {
        return view('admin.target-templates.edit', [
            'template' => $targetTemplate,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TargetTemplate $targetTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'template_id' => 'required|string|unique:target_templates,template_id,' . $targetTemplate->id,
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'constellation' => 'required|string|max:100',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'short_description' => 'required|string',
            'full_description' => 'nullable|string',
            'tips' => 'nullable|string',
            'preview_image' => 'nullable|image|max:5120',
            'thumbnail_image' => 'nullable|image|max:2048',
            'ra_hours' => 'required|integer|min:0|max:23',
            'ra_minutes' => 'required|integer|min:0|max:59',
            'ra_seconds' => 'required|numeric|min:0|max:59.9',
            'dec_degrees' => 'required|integer|min:-90|max:90',
            'dec_minutes' => 'required|integer|min:0|max:59',
            'dec_seconds' => 'required|numeric|min:0|max:59.9',
            'best_months' => 'required|array',
            'estimated_time' => 'nullable|string|max:50',
            'recommended_shots' => 'nullable|array',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer',
            'tags' => 'nullable|array',
        ]);

        // Handle image uploads
        if ($request->hasFile('preview_image')) {
            // Delete old image
            if ($targetTemplate->preview_image) {
                Storage::disk('public')->delete($targetTemplate->preview_image);
            }
            $validated['preview_image'] = $request->file('preview_image')->store('target-templates', 'public');
        }

        if ($request->hasFile('thumbnail_image')) {
            // Delete old thumbnail
            if ($targetTemplate->thumbnail_image) {
                Storage::disk('public')->delete($targetTemplate->thumbnail_image);
            }
            $validated['thumbnail_image'] = $request->file('thumbnail_image')->store('target-templates/thumbnails', 'public');
        }

        $targetTemplate->update($validated);

        return redirect()->route('admin.target-templates.index')
            ->with('success', 'Template mis à jour avec succès!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TargetTemplate $targetTemplate): RedirectResponse
    {
        // Delete associated images
        if ($targetTemplate->preview_image) {
            Storage::disk('public')->delete($targetTemplate->preview_image);
        }
        if ($targetTemplate->thumbnail_image) {
            Storage::disk('public')->delete($targetTemplate->thumbnail_image);
        }

        $targetTemplate->delete();

        return redirect()->route('admin.target-templates.index')
            ->with('success', 'Template supprimé avec succès!');
    }
}
