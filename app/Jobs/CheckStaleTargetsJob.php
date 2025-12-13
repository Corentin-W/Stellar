<?php

namespace App\Jobs;

use App\Models\RoboTarget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckStaleTargetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Timeout en heures pour considérer une cible comme "stale"
     */
    protected int $timeoutHours;

    /**
     * Create a new job instance.
     */
    public function __construct(int $timeoutHours = 48)
    {
        $this->timeoutHours = $timeoutHours;
    }

    /**
     * Execute the job.
     *
     * Vérifie toutes les cibles en statut "active" ou "executing"
     * qui n'ont pas été mises à jour depuis X heures.
     */
    public function handle(): void
    {
        $cutoffTime = now()->subHours($this->timeoutHours);

        // Récupérer les cibles "stale"
        $staleTargets = RoboTarget::whereIn('status', [
            RoboTarget::STATUS_ACTIVE,
            RoboTarget::STATUS_EXECUTING,
        ])
        ->where('updated_at', '<', $cutoffTime)
        ->with('user')
        ->get();

        if ($staleTargets->isEmpty()) {
            Log::info('[CheckStaleTargets] Aucune cible stale trouvée');
            return;
        }

        Log::info('[CheckStaleTargets] Traitement de ' . $staleTargets->count() . ' cibles stale');

        foreach ($staleTargets as $target) {
            try {
                // Marquer comme error
                $target->markAsError();

                // Refund les crédits
                if ($target->credits_held > 0) {
                    $target->refundCredits();

                    Log::warning('[CheckStaleTargets] Timeout refund', [
                        'target_id' => $target->id,
                        'target_guid' => $target->guid,
                        'target_name' => $target->target_name,
                        'user_id' => $target->user_id,
                        'credits_refunded' => $target->credits_held,
                        'last_update' => $target->updated_at->toDateTimeString(),
                        'hours_since_update' => $target->updated_at->diffInHours(now()),
                    ]);
                }

                // TODO: Envoyer notification à l'utilisateur
                // Notification::send($target->user, new TargetTimeoutNotification($target));

            } catch (\Exception $e) {
                Log::error('[CheckStaleTargets] Erreur lors du traitement', [
                    'target_id' => $target->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('[CheckStaleTargets] Traitement terminé', [
            'targets_processed' => $staleTargets->count(),
        ]);
    }

    /**
     * Tags pour Horizon (si utilisé)
     */
    public function tags(): array
    {
        return ['robotarget', 'cleanup', 'stale-targets'];
    }
}
