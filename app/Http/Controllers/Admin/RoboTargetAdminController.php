<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RoboTargetSetService;
use Illuminate\Http\Request;

class RoboTargetAdminController extends Controller
{
    private RoboTargetSetService $setService;

    public function __construct(RoboTargetSetService $setService)
    {
        $this->setService = $setService;
    }

    /**
     * Page principale de gestion des Sets
     */
    public function sets()
    {
        // Récupérer les Sets pour affichage initial
        $setsData = $this->setService->getSets();

        return view('admin.robotarget.sets', [
            'initialSets' => $setsData['sets'] ?? [],
            'connectionStatus' => $this->setService->getConnectionStatus()
        ]);
    }

    /**
     * API pour rafraîchir les Sets
     */
    public function apiGetSets(Request $request)
    {
        $profileName = $request->query('profile_name');
        return response()->json($this->setService->getSets($profileName));
    }

    /**
     * API pour créer un Set
     */
    public function apiCreateSet(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'profile_name' => 'required|string',
            'is_default' => 'boolean',
            'status' => 'integer|in:0,1',
            'tag' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        return response()->json($this->setService->addSet($validated));
    }

    /**
     * API pour mettre à jour un Set
     */
    public function apiUpdateSet(Request $request, string $guid)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'integer|in:0,1',
            'tag' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        return response()->json($this->setService->updateSet($guid, $validated));
    }

    /**
     * API pour supprimer un Set
     */
    public function apiDeleteSet(string $guid)
    {
        return response()->json($this->setService->deleteSet($guid));
    }

    /**
     * API pour activer/désactiver un Set
     */
    public function apiToggleSet(string $guid, Request $request)
    {
        $enable = $request->input('enable', true);
        return response()->json($this->setService->toggleSetStatus($guid, $enable));
    }
}
