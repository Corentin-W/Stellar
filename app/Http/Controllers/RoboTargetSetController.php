<?php

namespace App\Http\Controllers;

use App\Services\RoboTargetSetService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoboTargetSetController extends Controller
{
    private RoboTargetSetService $setService;

    public function __construct(RoboTargetSetService $setService)
    {
        $this->setService = $setService;
    }

    /**
     * Liste tous les Sets ou les Sets d'un profil
     * GET /api/robotarget/sets
     * Query params: ?profile_name=nom_du_profil (optionnel)
     */
    public function index(Request $request): JsonResponse
    {
        $profileName = $request->query('profile_name');
        $result = $this->setService->getSets($profileName);

        return response()->json($result);
    }

    /**
     * Récupère un Set par son GUID
     * GET /api/robotarget/sets/{guid}
     */
    public function show(string $guid): JsonResponse
    {
        $set = $this->setService->getSetByGuid($guid);

        if (!$set) {
            return response()->json([
                'success' => false,
                'error' => 'Set not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'set' => $set
        ]);
    }

    /**
     * Crée un nouveau Set
     * POST /api/robotarget/sets
     *
     * Body:
     * {
     *   "name": "Mon Set",
     *   "profile_name": "Profile.v2y",
     *   "is_default": false,
     *   "status": 0,
     *   "tag": "tag",
     *   "note": "note"
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'profile_name' => 'required|string',
            'is_default' => 'boolean',
            'status' => 'integer|in:0,1',
            'tag' => 'nullable|string',
            'note' => 'nullable|string',
            'guid' => 'nullable|uuid'
        ]);

        $result = $this->setService->addSet($validated);

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Met à jour un Set existant
     * PUT /api/robotarget/sets/{guid}
     *
     * Body:
     * {
     *   "name": "Nouveau nom",
     *   "status": 0,
     *   "tag": "nouveau tag",
     *   "note": "nouvelle note"
     * }
     */
    public function update(Request $request, string $guid): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'integer|in:0,1',
            'tag' => 'nullable|string',
            'note' => 'nullable|string'
        ]);

        $result = $this->setService->updateSet($guid, $validated);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Supprime un Set
     * DELETE /api/robotarget/sets/{guid}
     */
    public function destroy(string $guid): JsonResponse
    {
        $result = $this->setService->deleteSet($guid);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Active un Set
     * POST /api/robotarget/sets/{guid}/enable
     */
    public function enable(string $guid): JsonResponse
    {
        $result = $this->setService->toggleSetStatus($guid, true);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Désactive un Set
     * POST /api/robotarget/sets/{guid}/disable
     */
    public function disable(string $guid): JsonResponse
    {
        $result = $this->setService->toggleSetStatus($guid, false);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Récupère le statut de connexion Voyager
     * GET /api/robotarget/status
     */
    public function status(): JsonResponse
    {
        $result = $this->setService->getConnectionStatus();

        return response()->json($result);
    }

    /**
     * Récupère les Sets par profil
     * GET /api/robotarget/profiles/{profileName}/sets
     */
    public function byProfile(string $profileName): JsonResponse
    {
        $result = $this->setService->getSetsByProfile($profileName);

        return response()->json($result);
    }
}
