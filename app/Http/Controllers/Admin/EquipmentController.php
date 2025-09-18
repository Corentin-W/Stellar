<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EquipmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Equipment::query();

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        $equipment = $query->ordered()->paginate(10);

        return view('admin.equipment.index', compact('equipment'));
    }

    public function create(): View
    {
        return view('admin.equipment.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(array_keys(Equipment::TYPES))],
            'status' => ['required', Rule::in(array_keys(Equipment::STATUSES))],
            'location' => 'nullable|string|max:255',
            'price_per_hour_credits' => 'nullable|integer|min:0',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'images.*' => 'nullable|image|max:5120', // 5MB max
            'videos.*' => 'nullable|mimes:mp4,mov,avi,wmv|max:51200', // 50MB max
            'spec_keys.*' => 'nullable|string',
            'spec_values.*' => 'nullable|string'
        ]);

        // Traitement des spécifications
        $specifications = [];
        if ($request->filled('spec_keys') && $request->filled('spec_values')) {
            $keys = $request->spec_keys;
            $values = $request->spec_values;

            for ($i = 0; $i < count($keys); $i++) {
                if (!empty($keys[$i]) && !empty($values[$i])) {
                    $specifications[$keys[$i]] = $values[$i];
                }
            }
        }
        $validated['specifications'] = $specifications;

        // Nettoyage des champs non nécessaires
        unset($validated['spec_keys'], $validated['spec_values']);

        // Création de l'équipement
        $equipment = Equipment::create($validated);

        // Upload des images
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('equipment/images', 'public');
                $images[] = $path;
            }
            $equipment->update(['images' => $images]);
        }

        // Upload des vidéos
        if ($request->hasFile('videos')) {
            $videos = [];
            foreach ($request->file('videos') as $video) {
                $path = $video->store('equipment/videos', 'public');
                $videos[] = $path;
            }
            $equipment->update(['videos' => $videos]);
        }

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Équipement créé avec succès.');
    }

    public function show(Equipment $equipment): View
    {
        return view('admin.equipment.show', compact('equipment'));
    }

    public function edit(Equipment $equipment): View
    {
        return view('admin.equipment.edit', compact('equipment'));
    }

    public function update(Request $request, Equipment $equipment): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(array_keys(Equipment::TYPES))],
            'status' => ['required', Rule::in(array_keys(Equipment::STATUSES))],
            'location' => 'nullable|string|max:255',
            'price_per_hour_credits' => 'nullable|integer|min:0',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'new_images.*' => 'nullable|image|max:5120',
            'new_videos.*' => 'nullable|mimes:mp4,mov,avi,wmv|max:51200',
            'spec_keys.*' => 'nullable|string',
            'spec_values.*' => 'nullable|string',
            'remove_images' => 'nullable|array',
            'remove_videos' => 'nullable|array'
        ]);

        // Traitement des spécifications
        $specifications = [];
        if ($request->filled('spec_keys') && $request->filled('spec_values')) {
            $keys = $request->spec_keys;
            $values = $request->spec_values;

            for ($i = 0; $i < count($keys); $i++) {
                if (!empty($keys[$i]) && !empty($values[$i])) {
                    $specifications[$keys[$i]] = $values[$i];
                }
            }
        }
        $validated['specifications'] = $specifications;

        // Suppression des images sélectionnées
        if ($request->filled('remove_images')) {
            $currentImages = $equipment->images ?? [];
            foreach ($request->remove_images as $imageToRemove) {
                if (in_array($imageToRemove, $currentImages)) {
                    Storage::disk('public')->delete($imageToRemove);
                    $equipment->removeImage($imageToRemove);
                }
            }
        }

        // Suppression des vidéos sélectionnées
        if ($request->filled('remove_videos')) {
            $currentVideos = $equipment->videos ?? [];
            foreach ($request->remove_videos as $videoToRemove) {
                if (in_array($videoToRemove, $currentVideos)) {
                    Storage::disk('public')->delete($videoToRemove);
                    $equipment->removeVideo($videoToRemove);
                }
            }
        }

        // Ajout de nouvelles images
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $image) {
                $path = $image->store('equipment/images', 'public');
                $equipment->addImage($path);
            }
        }

        // Ajout de nouvelles vidéos
        if ($request->hasFile('new_videos')) {
            foreach ($request->file('new_videos') as $video) {
                $path = $video->store('equipment/videos', 'public');
                $equipment->addVideo($path);
            }
        }

        // Nettoyage des champs non nécessaires avant la mise à jour
        unset($validated['new_images'], $validated['new_videos'], $validated['remove_images'], $validated['remove_videos'], $validated['spec_keys'], $validated['spec_values']);

        // Mise à jour de l'équipement
        $equipment->update($validated);

        return redirect()->route('admin.equipment.show', $equipment)
            ->with('success', 'Équipement mis à jour avec succès.');
    }

    public function destroy(Equipment $equipment): RedirectResponse
    {
        // Suppression des fichiers
        if ($equipment->images) {
            foreach ($equipment->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        if ($equipment->videos) {
            foreach ($equipment->videos as $video) {
                Storage::disk('public')->delete($video);
            }
        }

        $equipment->delete();

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Équipement supprimé avec succès.');
    }

    public function toggleStatus(Equipment $equipment): RedirectResponse
    {
        $newStatus = match($equipment->status) {
            'available' => 'unavailable',
            'unavailable' => 'available',
            'maintenance' => 'available',
            'reserved' => 'available',
            default => 'available'
        };

        $equipment->update(['status' => $newStatus]);

        return back()->with('success', 'Statut mis à jour avec succès.');
    }

    public function toggleActive(Equipment $equipment): RedirectResponse
    {
        $equipment->update(['is_active' => !$equipment->is_active]);

        $status = $equipment->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Équipement {$status} avec succès.");
    }

    public function toggleFeatured(Equipment $equipment): RedirectResponse
    {
        $equipment->update(['is_featured' => !$equipment->is_featured]);

        $status = $equipment->is_featured ? 'mis en avant' : 'retiré de la mise en avant';
        return back()->with('success', "Équipement {$status} avec succès.");
    }
}
