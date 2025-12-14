<?php

namespace App\Services;

use App\Models\User;
use App\Models\RoboTarget;
use App\Models\RoboTargetShot;
use App\Models\RoboTargetSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RoboTargetService
{
    protected PricingEngine $pricingEngine;

    public function __construct(PricingEngine $pricingEngine)
    {
        $this->pricingEngine = $pricingEngine;
    }

    /**
     * Create a new RoboTarget with shots
     */
    public function createTarget(User $user, array $data): RoboTarget
    {
        // Vérifier que l'utilisateur a un abonnement actif
        if (!$user->subscription || !$user->subscription->isActive()) {
            throw new \Exception('Aucun abonnement actif');
        }

        $subscription = $user->subscription;

        // Calculer le coût
        $pricing = $this->pricingEngine->calculateCost($subscription, $data);

        // Vérifier que l'utilisateur a assez de crédits
        if ($user->credits_balance < $pricing['final_cost']) {
            throw new \Exception(
                "Crédits insuffisants. Requis: {$pricing['final_cost']}, Disponible: {$user->credits_balance}"
            );
        }

        // Créer la cible et les shots en transaction
        return DB::transaction(function () use ($user, $data, $pricing) {
            // Créer la cible
            $target = RoboTarget::create([
                'user_id' => $user->id,
                'target_name' => $data['target_name'],
                'ra_j2000' => $data['ra_j2000'],
                'dec_j2000' => $data['dec_j2000'],
                'priority' => $data['priority'] ?? 0,
                'c_moon_down' => $data['c_moon_down'] ?? false,
                'c_hfd_mean_limit' => $data['c_hfd_mean_limit'] ?? null,
                'c_alt_min' => $data['c_alt_min'] ?? 30,
                'c_ha_start' => $data['c_ha_start'] ?? -12,
                'c_ha_end' => $data['c_ha_end'] ?? 12,
                'date_start' => $data['date_start'] ?? null,
                'date_end' => $data['date_end'] ?? null,
                'is_repeat' => $data['is_repeat'] ?? false,
                'repeat_count' => $data['repeat_count'] ?? null,
                'status' => RoboTarget::STATUS_PENDING,
                'estimated_credits' => $pricing['final_cost'],
            ]);

            // Générer C_Mask
            $target->c_mask = $target->generateConstraintMask();
            $target->save();

            // Créer les shots
            foreach ($data['shots'] as $index => $shotData) {
                RoboTargetShot::create([
                    'robo_target_id' => $target->id,
                    'filter_index' => $shotData['filter_index'],
                    'filter_name' => $shotData['filter_name'],
                    'exposure' => $shotData['exposure'],
                    'num' => $shotData['num'],
                    'gain' => $shotData['gain'] ?? 100,
                    'offset' => $shotData['offset'] ?? 50,
                    'bin' => $shotData['bin'] ?? 1,
                    'type' => $shotData['type'] ?? RoboTargetShot::TYPE_LIGHT,
                    'order' => $index,
                ]);
            }

            // Hold credits
            $target->holdCredits($pricing['final_cost']);

            return $target->fresh(['shots']);
        });
    }

    /**
     * Submit target to Voyager via Proxy
     */
    public function submitToVoyager(RoboTarget $target): array
    {
        $proxyUrl = config('services.voyager_proxy.url');

        if (!$proxyUrl) {
            throw new \Exception('Voyager Proxy URL not configured');
        }

        try {
            // 1. Créer le Set
            $setResponse = Http::post("{$proxyUrl}/api/robotarget/sets", [
                'guid_set' => $target->set_guid,
                'set_name' => "Set_{$target->target_name}",
            ]);

            if (!$setResponse->successful()) {
                throw new \Exception('Failed to create set: ' . $setResponse->body());
            }

            // 2. Ajouter la Target
            $targetPayload = $target->toVoyagerPayload();

            $targetResponse = Http::post("{$proxyUrl}/api/robotarget/targets", $targetPayload);

            if (!$targetResponse->successful()) {
                throw new \Exception('Failed to create target: ' . $targetResponse->body());
            }

            // 3. Activer la cible dans Voyager
            $activateResponse = Http::put("{$proxyUrl}/api/robotarget/targets/{$target->guid}/status", [
                'status' => 'active',
            ]);

            if (!$activateResponse->successful()) {
                throw new \Exception('Failed to activate target: ' . $activateResponse->body());
            }

            // 4. Marquer comme active dans notre DB
            $target->markAsActive();

            return [
                'success' => true,
                'target_guid' => $target->guid,
                'set_guid' => $target->set_guid,
                'status' => 'active',
            ];

        } catch (\Exception $e) {
            // En cas d'erreur, marquer comme error et refund
            $target->markAsError();
            $target->refundCredits();

            throw $e;
        }
    }

    /**
     * Handle session complete event from Voyager
     */
    public function handleSessionComplete(array $eventData): void
    {
        $targetGuid = $eventData['guid_target'] ?? null;

        if (!$targetGuid) {
            throw new \Exception('Missing guid_target in event data');
        }

        $target = RoboTarget::where('guid', $targetGuid)->first();

        if (!$target) {
            throw new \Exception("Target not found: {$targetGuid}");
        }

        DB::transaction(function () use ($target, $eventData) {
            // Créer la session
            $session = RoboTargetSession::createFromVoyagerEvent($target, $eventData);

            // Mettre à jour le statut de la cible
            $session->updateTargetStatus();

            // Gérer les crédits (capture ou refund)
            $session->handleCredits();
        });
    }

    /**
     * Get user targets with filters
     */
    public function getUserTargets(User $user, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = RoboTarget::where('user_id', $user->id)
            ->with(['shots', 'sessions'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by date range
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->get();
    }

    /**
     * Get target statistics for user
     */
    public function getUserStats(User $user): array
    {
        $targets = RoboTarget::where('user_id', $user->id)->get();

        return [
            'total_targets' => $targets->count(),
            'pending' => $targets->where('status', RoboTarget::STATUS_PENDING)->count(),
            'active' => $targets->where('status', RoboTarget::STATUS_ACTIVE)->count(),
            'executing' => $targets->where('status', RoboTarget::STATUS_EXECUTING)->count(),
            'completed' => $targets->where('status', RoboTarget::STATUS_COMPLETED)->count(),
            'error' => $targets->where('status', RoboTarget::STATUS_ERROR)->count(),
            'aborted' => $targets->where('status', RoboTarget::STATUS_ABORTED)->count(),
            'total_credits_spent' => $targets->sum('credits_charged'),
            'total_credits_held' => $targets->sum('credits_held'),
        ];
    }

    /**
     * Cancel a target
     */
    public function cancelTarget(RoboTarget $target): void
    {
        if ($target->isCompleted() || $target->hasError() || $target->isAborted()) {
            throw new \Exception('Cannot cancel a target that is already finished');
        }

        DB::transaction(function () use ($target) {
            // Désactiver dans Voyager si active
            if ($target->isActive() || $target->isExecuting()) {
                $this->deactivateInVoyager($target);
            }

            // Marquer comme aborted
            $target->markAsAborted();

            // Refund credits
            $target->refundCredits();
        });
    }

    /**
     * Deactivate target in Voyager
     */
    protected function deactivateInVoyager(RoboTarget $target): void
    {
        $proxyUrl = config('services.voyager_proxy.url');

        if (!$proxyUrl) {
            return;
        }

        try {
            Http::put("{$proxyUrl}/api/robotarget/targets/{$target->guid}/status", [
                'status' => 'inactive',
            ]);
        } catch (\Exception $e) {
            // Log error but don't throw
            \Log::error('Failed to deactivate target in Voyager', [
                'target_guid' => $target->guid,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get target progress from Voyager
     */
    public function getTargetProgress(RoboTarget $target): ?array
    {
        $proxyUrl = config('services.voyager_proxy.url');

        if (!$proxyUrl) {
            return null;
        }

        try {
            $response = Http::get("{$proxyUrl}/api/robotarget/targets/{$target->guid}/progress");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Failed to get target progress', [
                'target_guid' => $target->guid,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
