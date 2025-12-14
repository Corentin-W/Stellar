<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Models\RoboTarget;
use App\Models\RoboTargetSession;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Récupérer les équipements vedettes pour la page welcome
        $featuredEquipment = Equipment::where('is_featured', true)
            ->where('is_active', true)
            ->where('status', 'available')
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        // Si moins de 4 équipements vedettes, compléter avec des équipements disponibles
        if ($featuredEquipment->count() < 4) {
            $additionalEquipment = Equipment::where('is_active', true)
                ->where('status', 'available')
                ->where('is_featured', false)
                ->orderBy('sort_order')
                ->take(4 - $featuredEquipment->count())
                ->get();

            $featuredEquipment = $featuredEquipment->concat($additionalEquipment);
        }
        return view('welcome', compact('featuredEquipment'));
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Statistiques globales
        $stats = [
            'total_targets' => RoboTarget::where('user_id', $user->id)->count(),
            'active_targets' => RoboTarget::where('user_id', $user->id)
                ->where('status', 'submitted')
                ->count(),
            'completed_sessions' => RoboTargetSession::whereHas('target', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('result', RoboTargetSession::RESULT_OK)->count(),
            'total_images' => RoboTargetSession::whereHas('target', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->sum('images_accepted'),
            'total_exposure_seconds' => RoboTargetSession::whereHas('target', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('result', RoboTargetSession::RESULT_OK)
                ->get()
                ->sum(function($session) {
                    return $session->getDuration() ?? 0;
                }),
            'credits_used_this_month' => DB::table('credit_transactions')
                ->where('user_id', $user->id)
                ->where('type', 'usage')
                ->whereMonth('created_at', now()->month)
                ->sum(DB::raw('ABS(credits_amount)')),
        ];

        // Dernières sessions complétées
        $recentSessions = RoboTargetSession::with('target')
            ->whereHas('target', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('result', RoboTargetSession::RESULT_OK)
            ->orderBy('session_end', 'desc')
            ->take(5)
            ->get();

        // Targets actives
        $activeTargets = RoboTarget::where('user_id', $user->id)
            ->where('status', 'submitted')
            ->with('sessions')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Distribution des filtres (shots les plus utilisés)
        $filterDistribution = DB::table('robo_target_shots')
            ->join('robo_targets', 'robo_target_shots.target_id', '=', 'robo_targets.id')
            ->where('robo_targets.user_id', $user->id)
            ->select('filter_name', DB::raw('SUM(num) as total_shots'))
            ->groupBy('filter_name')
            ->orderByDesc('total_shots')
            ->get();

        return view('dashboard', [
            'user' => $user,
            'subscription' => $user->subscription,
            'stats' => $stats,
            'recentSessions' => $recentSessions,
            'activeTargets' => $activeTargets,
            'filterDistribution' => $filterDistribution,
        ]);
    }
}
