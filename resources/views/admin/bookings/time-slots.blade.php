@extends('layouts.astral-app')

@section('title', 'Plages Horaires - ' . $equipment->name)

@section('content')
<div class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <a href="{{ route('admin.equipment.index') }}" class="text-white/60 hover:text-white transition-colors">
                    ← Retour aux équipements
                </a>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">⏰ Plages Horaires</h1>
            <p class="text-white/60">{{ $equipment->name }}</p>
        </div>

        @if(session('success'))
        <div class="dashboard-card p-4 mb-6 bg-green-500/20 border-green-500/50">
            <p class="text-green-400">✅ {{ session('success') }}</p>
        </div>
        @endif

        <!-- Créer une nouvelle plage -->
        <div class="dashboard-card p-6 mb-8">
            <h2 class="text-xl font-bold text-white mb-4">➕ Ajouter une plage horaire</h2>

            <form action="{{ route('admin.bookings.time-slots.store', $equipment) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                @csrf

                <div>
                    <label class="block text-white font-medium mb-2">Jour de la semaine</label>
                    <select name="day_of_week" required class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                        <option value="0">Dimanche</option>
                        <option value="1" selected>Lundi</option>
                        <option value="2">Mardi</option>
                        <option value="3">Mercredi</option>
                        <option value="4">Jeudi</option>
                        <option value="5">Vendredi</option>
                        <option value="6">Samedi</option>
                    </select>
                </div>

                <div>
                    <label class="block text-white font-medium mb-2">Heure de début</label>
                    <input type="time" name="start_time" value="20:00" required class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                </div>

                <div>
                    <label class="block text-white font-medium mb-2">Heure de fin</label>
                    <input type="time" name="end_time" value="23:00" required class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                </div>

                <div>
                    <label class="block text-white font-medium mb-2">Réservations max</label>
                    <input type="number" name="max_concurrent_bookings" value="1" min="1" max="10" required class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full px-6 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold hover:from-purple-700 hover:to-pink-700 transition-all">
                        Ajouter
                    </button>
                </div>
            </form>
        </div>

        <!-- Liste des plages existantes -->
        <div class="dashboard-card p-6">
            <h2 class="text-xl font-bold text-white mb-6">📋 Plages horaires configurées</h2>

            @if($timeSlots->count() > 0)
            <div class="space-y-3">
                @foreach(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'] as $index => $dayName)
                    @php
                        $dayIndex = $index === 6 ? 0 : $index + 1; // Dimanche = 0
                        $daySlots = $timeSlots->where('day_of_week', $dayIndex);
                    @endphp

                    @if($daySlots->count() > 0)
                    <div class="border border-white/10 rounded-lg p-4 bg-white/5">
                        <h3 class="text-white font-semibold mb-3">{{ $dayName }}</h3>
                        <div class="space-y-2">
                            @foreach($daySlots as $slot)
                            <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                                <div class="flex items-center gap-4">
                                    <div class="text-white font-medium">
                                        {{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}
                                    </div>
                                    <div class="text-white/60 text-sm">
                                        Max: {{ $slot->max_concurrent_bookings }} réservation(s)
                                    </div>
                                    @if($slot->is_active)
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400">
                                        ✓ Active
                                    </span>
                                    @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-500/20 text-gray-400">
                                        ✗ Inactive
                                    </span>
                                    @endif
                                </div>

                                <form action="{{ route('admin.bookings.time-slots.destroy', $slot) }}" method="POST" onsubmit="return confirm('Supprimer cette plage horaire ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors text-sm font-medium">
                                        🗑️ Supprimer
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <div class="w-20 h-20 rounded-full bg-white/5 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Aucune plage horaire</h3>
                <p class="text-white/60">Ajoutez des plages horaires pour permettre les réservations</p>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
