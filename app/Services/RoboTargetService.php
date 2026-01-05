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
        $proxyUrl = config('services.voyager.proxy_url');
        $apiKey = config('services.voyager.proxy_api_key');

        if (!$proxyUrl) {
            throw new \Exception('Voyager Proxy URL not configured');
        }

        if (!$apiKey) {
            throw new \Exception('Voyager Proxy API key not configured');
        }

        \Log::info('🚀 [RoboTargetService] Submitting to Voyager', [
            'target_id' => $target->id,
            'target_guid' => $target->guid,
            'set_guid' => $target->set_guid,
            'proxy_url' => $proxyUrl,
        ]);

        try {
            // Prepare HTTP client with API key
            $http = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(30);

            \Log::info('🔐 [RoboTargetService] HTTP Client configured', [
                'proxy_url' => $proxyUrl,
                'api_key_present' => !empty($apiKey),
                'api_key_preview' => substr($apiKey, 0, 20) . '...',
            ]);

            // 1. Récupérer une BaseSequence d'abord (pour obtenir le ProfileName)
            \Log::info('📋 [RoboTargetService] Fetching BaseSequences from Voyager');

            $baseSeqResponse = $http->get("{$proxyUrl}/api/robotarget/base-sequences");

            if (!$baseSeqResponse->successful()) {
                \Log::error('❌ [RoboTargetService] Failed to fetch BaseSequences', [
                    'status' => $baseSeqResponse->status(),
                    'body' => $baseSeqResponse->body(),
                ]);
                throw new \Exception('Failed to fetch BaseSequences: ' . $baseSeqResponse->body());
            }

            $baseSeqData = $baseSeqResponse->json();
            $sequences = $baseSeqData['sequences'] ?? [];

            if (empty($sequences)) {
                \Log::error('❌ [RoboTargetService] No BaseSequences found in Voyager');
                throw new \Exception('No BaseSequences found in Voyager. Please create a sequence first.');
            }

            // Utiliser la première séquence disponible
            $selectedSequence = $sequences[0];
            $baseSequenceGuid = $selectedSequence['GuidBaseSequence'];
            $profileName = $selectedSequence['ProfileName'] ?? '';

            \Log::info('✅ [RoboTargetService] BaseSequence selected', [
                'guid' => $baseSequenceGuid,
                'name' => $selectedSequence['NameSeq'] ?? 'Unknown',
                'profile' => $profileName,
            ]);

            // 2. Créer le Set avec le ProfileName de la BaseSequence
            $setPayload = [
                'Guid' => $target->set_guid,
                'Name' => "Set_{$target->target_name}",
                'ProfileName' => $profileName, // IMPORTANT: Utiliser le ProfileName de la BaseSequence pour éviter NullReferenceException
                'IsDefault' => false,
                'Tag' => '', // OBLIGATOIRE même si vide
                'Status' => 0,
                'Note' => '',
            ];

            \Log::info('📦 [RoboTargetService] Creating Set in Voyager', [
                'set_guid' => $target->set_guid,
                'set_name' => "Set_{$target->target_name}",
                'profile_name' => $profileName,
                'url' => "{$proxyUrl}/api/robotarget/sets",
                'payload' => $setPayload,
            ]);

            \Log::info('⏱️  [RoboTargetService] Sending POST request to proxy...');

            $setResponse = $http->post("{$proxyUrl}/api/robotarget/sets", $setPayload);

            \Log::info('📨 [RoboTargetService] Received response from proxy', [
                'status' => $setResponse->status(),
                'successful' => $setResponse->successful(),
                'body_preview' => substr($setResponse->body(), 0, 200),
            ]);

            if (!$setResponse->successful()) {
                \Log::error('❌ [RoboTargetService] Failed to create set', [
                    'status' => $setResponse->status(),
                    'body' => $setResponse->body(),
                ]);
                throw new \Exception('Failed to create set: ' . $setResponse->body());
            }

            \Log::info('✅ [RoboTargetService] Set created in Voyager');

            // 3. Ajouter la Target (SANS les shots)
            $targetPayload = $target->toVoyagerPayload($baseSequenceGuid);
            $shots = $targetPayload['Shots'] ?? [];
            unset($targetPayload['Shots']); // Les shots seront ajoutés séparément

            // Supprimer UNIQUEMENT les champs vides qui ne sont PAS obligatoires
            // IMPORTANT: Certains champs STRING sont OBLIGATOIRES même s'ils sont vides (Voyager les requiert pour le parsing)
            $mandatoryFields = [
                'GuidTarget',
                'RefGuidSet',
                'RefGuidBaseSequence',
                'TargetName',
                'Tag',  // OBLIGATOIRE même si vide (string)
                'Note', // OBLIGATOIRE même si vide (string)
                'RAJ2000',
                'DECJ2000',
                'TType',
                'TKey',  // OBLIGATOIRE même si vide (string)
                'TName', // OBLIGATOIRE même si vide (string)
                'C_ID',  // OBLIGATOIRE - GUID du set de contraintes
                'C_Mask', // OBLIGATOIRE - Masque binaire des contraintes actives
                'C_DateStart', // OBLIGATOIRE - Contrainte de date de début (0 = pas de contrainte)
                'C_DateEnd', // OBLIGATOIRE - Contrainte de date de fin (0 = pas de contrainte)
                'C_TimeStart', // OBLIGATOIRE - Contrainte d'heure de début (0 = pas de contrainte)
                'C_TimeEnd', // OBLIGATOIRE - Contrainte d'heure de fin (0 = pas de contrainte)
                'C_Mask2', // OBLIGATOIRE - Masque des contraintes secondaires spécialisées (string vide)
                'Token', // OBLIGATOIRE - Reserved OpenSkyGems (string vide)
            ];
            $targetPayload = array_filter($targetPayload, function ($value, $key) use ($mandatoryFields) {
                // Garder les champs obligatoires même s'ils sont vides
                if (in_array($key, $mandatoryFields)) {
                    return true;
                }
                // Pour les autres, supprimer si vide
                return $value !== '' && $value !== null;
            }, ARRAY_FILTER_USE_BOTH);

            \Log::info('🎯 [RoboTargetService] Adding Target to Voyager', [
                'url' => "{$proxyUrl}/api/robotarget/targets",
                'payload' => $targetPayload,
                'fields_count' => count($targetPayload),
            ]);

            \Log::info('⏱️  [RoboTargetService] Sending POST request for Target...');

            $targetResponse = $http->post("{$proxyUrl}/api/robotarget/targets", $targetPayload);

            \Log::info('📨 [RoboTargetService] Received Target response from proxy', [
                'status' => $targetResponse->status(),
                'successful' => $targetResponse->successful(),
                'body_preview' => substr($targetResponse->body(), 0, 200),
            ]);

            if (!$targetResponse->successful()) {
                \Log::error('❌ [RoboTargetService] Failed to create target', [
                    'status' => $targetResponse->status(),
                    'body' => $targetResponse->body(),
                ]);
                throw new \Exception('Failed to create target: ' . $targetResponse->body());
            }

            \Log::info('✅ [RoboTargetService] Target created in Voyager');

            // 4. Ajouter les shots
            foreach ($shots as $index => $shot) {
                \Log::info("📸 [RoboTargetService] Adding shot {$index} to target");

                $shotResponse = $http->post("{$proxyUrl}/api/robotarget/shots", array_merge($shot, [
                    'RefGuidTarget' => $target->guid,
                ]));

                if (!$shotResponse->successful()) {
                    \Log::error('❌ [RoboTargetService] Failed to create shot', [
                        'shot_index' => $index,
                        'status' => $shotResponse->status(),
                        'body' => $shotResponse->body(),
                    ]);
                    throw new \Exception("Failed to create shot {$index}: " . $shotResponse->body());
                }
            }

            \Log::info('✅ [RoboTargetService] All shots added to target');

            // 5. Activer la cible dans Voyager
            \Log::info('⚡ [RoboTargetService] Activating target in Voyager');

            $activateResponse = $http->put("{$proxyUrl}/api/robotarget/targets/{$target->guid}/status", [
                'status' => 'active',
            ]);

            if (!$activateResponse->successful()) {
                \Log::error('❌ [RoboTargetService] Failed to activate target', [
                    'status' => $activateResponse->status(),
                    'body' => $activateResponse->body(),
                ]);
                throw new \Exception('Failed to activate target: ' . $activateResponse->body());
            }

            \Log::info('✅ [RoboTargetService] Target activated in Voyager');

            // 6. Marquer comme active dans notre DB
            $target->markAsActive();

            \Log::info('🎉 [RoboTargetService] Target fully submitted and active', [
                'target_id' => $target->id,
                'status' => 'active',
            ]);

            return [
                'success' => true,
                'target_guid' => $target->guid,
                'set_guid' => $target->set_guid,
                'status' => 'active',
            ];

        } catch (\Exception $e) {
            \Log::error('❌ [RoboTargetService] Submission failed', [
                'target_id' => $target->id,
                'error' => $e->getMessage(),
            ]);

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
