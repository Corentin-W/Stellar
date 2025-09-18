<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;

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

    public function dashboard()
    {
        return view('dashboard');
    }
}
