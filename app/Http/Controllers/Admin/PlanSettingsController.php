<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanSetting;
use Illuminate\Http\Request;

class PlanSettingsController extends Controller
{
    public function index()
    {
        $plans = PlanSetting::all();

        return view('admin.plans.index', compact('plans'));
    }

    public function edit($id)
    {
        $plan = PlanSetting::findOrFail($id);

        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, $id)
    {
        $plan = PlanSetting::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'credits_per_month' => 'required|integer|min:1',
            'trial_days' => 'required|integer|min:0|max:365',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'stripe_price_id' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $plan->update($validated);

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Plan mis à jour avec succès');
    }
}
