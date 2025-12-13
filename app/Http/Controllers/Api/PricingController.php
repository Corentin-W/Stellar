<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PricingEngine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PricingController extends Controller
{
    protected PricingEngine $pricingEngine;

    public function __construct(PricingEngine $pricingEngine)
    {
        $this->pricingEngine = $pricingEngine;
    }

    /**
     * Get price estimate for a target configuration
     */
    public function estimate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subscription_plan' => 'required|in:stardust,nebula,quasar',
            'target' => 'required|array',
            'target.priority' => 'required|integer|min:0|max:4',
            'target.c_moon_down' => 'nullable|boolean',
            'target.c_hfd_mean_limit' => 'nullable|numeric|min:1.5|max:4.0',
            'target.shots' => 'required|array|min:1',
            'target.shots.*.exposure' => 'required|integer|min:1',
            'target.shots.*.num' => 'required|integer|min:1',
        ]);

        try {
            $estimation = $this->pricingEngine->getEstimate(
                $validated['subscription_plan'],
                $validated['target']
            );

            return response()->json([
                'success' => true,
                'estimation' => $estimation,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get subscription recommendation based on monthly targets
     */
    public function recommend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'monthly_targets' => 'required|array|min:1',
            'monthly_targets.*.priority' => 'required|integer|min:0|max:4',
            'monthly_targets.*.c_moon_down' => 'nullable|boolean',
            'monthly_targets.*.c_hfd_mean_limit' => 'nullable|numeric',
            'monthly_targets.*.shots' => 'required|array|min:1',
            'monthly_targets.*.shots.*.exposure' => 'required|integer|min:1',
            'monthly_targets.*.shots.*.num' => 'required|integer|min:1',
        ]);

        try {
            $recommendedPlan = $this->pricingEngine->getSubscriptionRecommendation(
                $validated['monthly_targets']
            );

            return response()->json([
                'success' => true,
                'recommended_plan' => $recommendedPlan,
                'message' => "Nous vous recommandons le plan {$recommendedPlan}",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
