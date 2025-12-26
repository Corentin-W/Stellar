<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RoboTargetShotService;
use Illuminate\Http\Request;

class RoboTargetShotController extends Controller
{
    private RoboTargetShotService $shotService;

    public function __construct(RoboTargetShotService $shotService)
    {
        $this->shotService = $shotService;
    }

    /**
     * Récupérer la configuration complète du matériel
     * GET /api/config/hardware?profile=Default.v2y
     */
    public function getHardwareConfig(Request $request)
    {
        $profileName = $request->query('profile');
        return response()->json($this->shotService->getHardwareConfiguration($profileName));
    }

    /**
     * Récupérer la configuration des filtres (rétrocompatibilité)
     * GET /api/config/filters
     */
    public function getFilterConfig()
    {
        return response()->json($this->shotService->getFilterConfiguration());
    }

    /**
     * Récupérer tous les profils disponibles
     * GET /api/config/profiles
     */
    public function getProfiles()
    {
        return response()->json($this->shotService->getAllProfiles());
    }

    /**
     * Récupérer la configuration d'un profil spécifique
     * GET /api/config/profiles/{profileName}
     */
    public function getProfileConfig(string $profileName)
    {
        return response()->json($this->shotService->getProfileConfiguration($profileName));
    }

    /**
     * Récupérer les détails d'un filtre spécifique
     * GET /api/config/filters/{filterIndex}
     */
    public function getFilterDetails(int $filterIndex)
    {
        $details = $this->shotService->getFilterDetails($filterIndex);

        if ($details === null) {
            return response()->json([
                'success' => false,
                'error' => 'Filter not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'filter' => $details
        ]);
    }

    /**
     * Récupérer les shots planifiés d'une Target
     * GET /api/targets/{targetGuid}/shots
     */
    public function getPlannedShots(string $targetGuid)
    {
        // Augmenter le timeout d'exécution PHP
        set_time_limit(90);

        return response()->json($this->shotService->getPlannedShots($targetGuid));
    }

    /**
     * Récupérer les images capturées d'une Target
     * GET /api/targets/{targetGuid}/shots-done?deleted=false
     */
    public function getCapturedShots(string $targetGuid, Request $request)
    {
        $isDeleted = $request->query('deleted', false);

        return response()->json($this->shotService->getCapturedShots($targetGuid, $isDeleted));
    }

    /**
     * Récupérer tous les shots (planifiés + capturés) d'une Target
     * GET /api/targets/{targetGuid}/shots-all
     */
    public function getAllShots(string $targetGuid)
    {
        // Augmenter le timeout d'exécution PHP
        set_time_limit(90);

        return response()->json($this->shotService->getAllShotsForTarget($targetGuid));
    }
}
