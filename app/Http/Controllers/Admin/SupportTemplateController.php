<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTemplate;
use App\Models\SupportCategory;
use Illuminate\Http\Request;

class SupportTemplateController extends Controller
{
    // Le middleware 'auth' et 'admin' sont déjà appliqués dans les routes

    /**
     * Liste des templates
     */
    public function index()
    {
        $templates = SupportTemplate::with(['category', 'creator'])
                                   ->orderBy('name')
                                   ->paginate(20);

        return view('admin.support.templates.index', compact('templates'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $categories = SupportCategory::active()->ordered()->get();
        return view('admin.support.templates.create', compact('categories'));
    }

    /**
     * Enregistrer un nouveau template
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:support_categories,id',
        ]);

        SupportTemplate::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'content' => $request->content,
            'category_id' => $request->category_id,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.support.templates.index')
                        ->with('success', 'Template créé avec succès.');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(SupportTemplate $template)
    {
        $categories = SupportCategory::active()->ordered()->get();
        return view('admin.support.templates.edit', compact('template', 'categories'));
    }

    /**
     * Mettre à jour un template
     */
    public function update(Request $request, SupportTemplate $template)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:support_categories,id',
        ]);

        $template->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'content' => $request->content,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('admin.support.templates.index')
                        ->with('success', 'Template mis à jour avec succès.');
    }

    /**
     * Activer/désactiver un template
     */
    public function toggleStatus(SupportTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);

        $status = $template->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Template {$status} avec succès.");
    }

    /**
     * Supprimer un template
     */
    public function destroy(SupportTemplate $template)
    {
        $template->delete();

        return redirect()->route('admin.support.templates.index')
                        ->with('success', 'Template supprimé avec succès.');
    }

    /**
     * Récupérer le contenu d'un template (AJAX)
     */
    public function getContent(SupportTemplate $template)
    {
        if (!$template->is_active) {
            return response()->json(['error' => 'Template désactivé.'], 403);
        }

        // Incrémenter le compteur d'utilisation
        $template->incrementUsage();

        return response()->json([
            'subject' => $template->subject,
            'content' => $template->content,
        ]);
    }
}
