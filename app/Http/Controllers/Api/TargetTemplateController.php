<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TargetTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TargetTemplateController extends Controller
{
    /**
     * Get all active target templates
     */
    public function index(Request $request): JsonResponse
    {
        $query = TargetTemplate::query()->active();

        // Filter by difficulty
        if ($request->has('difficulty') && $request->difficulty !== 'all') {
            $query->byDifficulty($request->difficulty);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('constellation', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderBy('display_order')
                          ->orderBy('name')
                          ->get()
                          ->map(function($template) {
                              return [
                                  'id' => $template->template_id,
                                  'name' => $template->name,
                                  'type' => $template->type,
                                  'constellation' => $template->constellation,
                                  'difficulty' => $template->difficulty,
                                  'description' => $template->short_description,
                                  'full_description' => $template->full_description,
                                  'tips' => $template->tips,
                                  'preview_image' => $template->preview_image ? url('storage/' . $template->preview_image) : null,
                                  'thumbnail_image' => $template->thumbnail_image ? url('storage/' . $template->thumbnail_image) : null,
                                  'gallery_images' => $template->gallery_images ? collect($template->gallery_images)->map(fn($img) => url('storage/' . $img))->toArray() : [],
                                  'ra_hours' => $template->ra_hours,
                                  'ra_minutes' => $template->ra_minutes,
                                  'ra_seconds' => (float) $template->ra_seconds,
                                  'dec_degrees' => $template->dec_degrees,
                                  'dec_minutes' => $template->dec_minutes,
                                  'dec_seconds' => (float) $template->dec_seconds,
                                  'best_months' => $template->best_months,
                                  'recommended_shots' => $template->recommended_shots,
                                  'estimated_time' => $template->estimated_time,
                                  'tags' => $template->tags ?? [],
                              ];
                          });

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }
}
