<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\RoboTarget;

class PricingEngine
{
    // Coût de base par heure
    const BASE_COST_PER_HOUR = 1.0; // 1 crédit = 1 heure

    /**
     * Overhead technique par pose (en secondes)
     *
     * Ce temps inclut :
     * - Lecture du capteur CCD/CMOS (~5-10s)
     * - Sauvegarde du fichier FITS (~5s)
     * - Vérification du guidage (~5-10s)
     * - Temps système divers (~5s)
     *
     * Total moyen : ~30 secondes par pose
     *
     * Ce paramètre assure que le coût reflète le temps RÉEL d'occupation
     * du télescope, pas seulement le temps d'exposition.
     */
    const OVERHEAD_PER_SHOT_SECONDS = 30;

    // Multiplicateurs Priority
    const MULTIPLIER_PRIORITY = [
        0 => 1.0,  // Very Low
        1 => 1.0,  // Low
        2 => 1.2,  // Normal
        3 => 2.0,  // High
        4 => 3.0,  // First
    ];

    // Multiplicateur Nuit noire (MoonDown)
    const MULTIPLIER_MOON_DOWN = 2.0;

    // Multiplicateur Garantie HFD
    const MULTIPLIER_HFD_GUARANTEE = 1.5;

    /**
     * Calculate total cost for a target
     */
    public function calculateCost(Subscription $subscription, array $targetConfig): array
    {
        // 1. Estimer la durée
        $estimatedHours = $this->estimateDuration($targetConfig);

        // 2. Calculer le coût de base
        $baseCost = $estimatedHours * self::BASE_COST_PER_HOUR;

        // 3. Calculer les multiplicateurs
        $multipliers = $this->calculateMultipliers($subscription, $targetConfig);

        // 4. Coût final
        $finalCost = (int) ceil($baseCost * $multipliers['total']);

        // 5. Vérifier les limites d'abonnement
        $this->validateSubscriptionLimits($subscription, $targetConfig);

        return [
            'estimated_hours' => $estimatedHours,
            'base_cost' => $baseCost,
            'multipliers' => $multipliers,
            'final_cost' => $finalCost,
            'breakdown' => [
                'base' => $baseCost,
                'priority_multiplier' => $multipliers['priority'],
                'moon_down_multiplier' => $multipliers['moon_down'],
                'hfd_multiplier' => $multipliers['hfd'],
                'total' => $finalCost,
            ]
        ];
    }

    /**
     * Estimate total duration in hours (exposure + technical overheads)
     *
     * Le calcul inclut :
     * 1. Le temps d'exposition pur (durée × nombre de poses)
     * 2. Les overheads techniques (OVERHEAD_PER_SHOT_SECONDS × nombre de poses)
     *
     * Exemple :
     *   10 poses de 5 minutes = 50min d'exposition + 5min d'overhead = 55min
     *
     * Cette approche garantit que le coût reflète le temps RÉEL d'occupation
     * du télescope, encourageant ainsi des stratégies d'acquisition optimales.
     */
    protected function estimateDuration(array $targetConfig): float
    {
        $shots = $targetConfig['shots'] ?? [];

        $totalExposureSeconds = 0;
        $totalOverheadSeconds = 0;

        foreach ($shots as $shot) {
            $numShots = $shot['num'] ?? 0;
            $exposureDuration = $shot['exposure'] ?? 0;

            // Temps d'exposition total pour ce filtre
            $totalExposureSeconds += $exposureDuration * $numShots;

            // Overhead technique pour ce filtre
            $totalOverheadSeconds += $numShots * self::OVERHEAD_PER_SHOT_SECONDS;
        }

        $totalSeconds = $totalExposureSeconds + $totalOverheadSeconds;

        // Convertir en heures et arrondir à 2 décimales
        return round($totalSeconds / 3600, 2);
    }

    /**
     * Calculate all multipliers
     */
    protected function calculateMultipliers(Subscription $subscription, array $targetConfig): array
    {
        $priority = $targetConfig['priority'] ?? 0;
        $moonDown = $targetConfig['c_moon_down'] ?? false;
        $hfdLimit = $targetConfig['c_hfd_mean_limit'] ?? null;

        $multipliers = [
            'priority' => self::MULTIPLIER_PRIORITY[$priority] ?? 1.0,
            'moon_down' => $moonDown ? self::MULTIPLIER_MOON_DOWN : 1.0,
            'hfd' => ($hfdLimit && $hfdLimit > 0) ? self::MULTIPLIER_HFD_GUARANTEE : 1.0,
        ];

        // Multiplicateur total
        $multipliers['total'] = $multipliers['priority'] * $multipliers['moon_down'] * $multipliers['hfd'];

        return $multipliers;
    }

    /**
     * Validate subscription limits
     */
    protected function validateSubscriptionLimits(Subscription $subscription, array $targetConfig): void
    {
        $priority = $targetConfig['priority'] ?? 0;
        $moonDown = $targetConfig['c_moon_down'] ?? false;
        $hfdLimit = $targetConfig['c_hfd_mean_limit'] ?? null;

        // Vérifier priority
        if (!$subscription->canUsePriority($priority)) {
            throw new \Exception(
                "Priority {$priority} not allowed for {$subscription->plan} plan. Max priority: {$subscription->getMaxPriority()}"
            );
        }

        // Vérifier moon down
        if ($moonDown && !$subscription->canUseMoonDown()) {
            throw new \Exception(
                "Moon down option not available for {$subscription->plan} plan"
            );
        }

        // Vérifier HFD
        if ($hfdLimit && !$subscription->canAdjustHFD()) {
            throw new \Exception(
                "HFD guarantee not available for {$subscription->plan} plan"
            );
        }
    }

    /**
     * Get price estimate without subscription validation
     */
    public function getEstimate(string $plan, array $targetConfig): array
    {
        $estimatedHours = $this->estimateDuration($targetConfig);
        $baseCost = $estimatedHours * self::BASE_COST_PER_HOUR;

        $priority = $targetConfig['priority'] ?? 0;
        $moonDown = $targetConfig['c_moon_down'] ?? false;
        $hfdLimit = $targetConfig['c_hfd_mean_limit'] ?? null;

        $multipliers = [
            'priority' => self::MULTIPLIER_PRIORITY[$priority] ?? 1.0,
            'moon_down' => $moonDown ? self::MULTIPLIER_MOON_DOWN : 1.0,
            'hfd' => ($hfdLimit && $hfdLimit > 0) ? self::MULTIPLIER_HFD_GUARANTEE : 1.0,
        ];

        $multipliers['total'] = $multipliers['priority'] * $multipliers['moon_down'] * $multipliers['hfd'];

        $finalCost = (int) ceil($baseCost * $multipliers['total']);

        return [
            'plan' => $plan,
            'estimated_hours' => round($estimatedHours, 2),
            'base_cost' => round($baseCost, 2),
            'multipliers' => $multipliers,
            'final_cost' => $finalCost,
            'details' => [
                'priority' => $priority,
                'moon_down' => $moonDown,
                'hfd_limit' => $hfdLimit,
            ]
        ];
    }

    /**
     * Calculate refund amount based on session result
     */
    public function calculateRefund(RoboTarget $target, int $result): array
    {
        // Result codes:
        // 1 = OK (pas de remboursement)
        // 2 = Aborted (remboursement partiel 50%)
        // 3 = Error (remboursement total 100%)

        $refundPercentage = match($result) {
            1 => 0,    // OK: pas de remboursement
            2 => 50,   // Aborted: 50% remboursé
            3 => 100,  // Error: 100% remboursé
            default => 100
        };

        $refundAmount = (int) ceil(($target->credits_held * $refundPercentage) / 100);

        return [
            'result' => $result,
            'refund_percentage' => $refundPercentage,
            'credits_held' => $target->credits_held,
            'refund_amount' => $refundAmount,
            'credits_charged' => $target->credits_held - $refundAmount,
        ];
    }

    /**
     * Get subscription recommendations
     */
    public function getSubscriptionRecommendation(array $monthlyTargets): string
    {
        $totalMonthlyCredits = 0;

        foreach ($monthlyTargets as $target) {
            $estimate = $this->getEstimate('nebula', $target);
            $totalMonthlyCredits += $estimate['final_cost'];
        }

        // Recommandations basées sur le total mensuel
        if ($totalMonthlyCredits <= 20) {
            return Subscription::STARDUST;
        } elseif ($totalMonthlyCredits <= 60) {
            return Subscription::NEBULA;
        } else {
            return Subscription::QUASAR;
        }
    }
}
