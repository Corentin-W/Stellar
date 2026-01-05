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

    /**
     * API pour récupérer toutes les Targets de tous les Sets
     */
    public function apiGetAllTargets(Request $request)
    {
        $setGuid = $request->query('set_guid');
        return response()->json($this->setService->getTargets($setGuid));
    }

    /**
     * API pour récupérer les Targets d'un Set spécifique
     */
    public function apiGetTargets(string $setGuid)
    {
        return response()->json($this->setService->getTargets($setGuid));
    }

    /**
     * API pour récupérer les Shots planifiés d'une Target
     */
    public function apiGetShots(string $targetGuid)
    {
        // Augmenter le timeout d'exécution PHP pour cette opération
        set_time_limit(90); // 90 secondes

        return response()->json($this->setService->getShots($targetGuid));
    }

    /**
     * API pour récupérer les images capturées d'une Target
     */
    public function apiGetShotDoneList(string $targetGuid, Request $request)
    {
        $isDeleted = $request->query('deleted', false);
        return response()->json($this->setService->getShotDoneList($targetGuid, $isDeleted));
    }

    /**
     * API pour récupérer la configuration des filtres
     */
    public function apiGetConfigDataShot()
    {
        return response()->json($this->setService->getConfigDataShot());
    }

    /**
     * API pour récupérer les Base Sequences (templates .s2q)
     */
    public function apiGetBaseSequences(Request $request)
    {
        // Augmenter le timeout d'exécution PHP (GetBaseSequence peut être très lent)
        set_time_limit(150);

        $profileName = $request->query('profile');
        return response()->json($this->setService->getBaseSequences($profileName));
    }
}
