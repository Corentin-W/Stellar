@extends('layouts.astral-app')

@section('title', 'Calendrier de Réservation')

@section('content')
<div class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">📅 Calendrier de Réservation</h1>
            <p class="text-white/60">Sélectionnez un équipement et choisissez vos créneaux</p>
        </div>

        <!-- Sélecteur d'équipement -->
        <div class="dashboard-card p-6 mb-6">
            <label class="block text-white font-medium mb-3">Équipement à réserver</label>
            <select id="equipment-select" class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                <option value="">-- Sélectionnez un équipement --</option>
                @foreach($equipments as $equipment)
                    <option value="{{ $equipment->id }}" {{ $selectedEquipment && $selectedEquipment->id == $equipment->id ? 'selected' : '' }}>
                        {{ $equipment->name }} ({{ $equipment->price_per_hour_credits }} crédits/heure)
                    </option>
                @endforeach
            </select>
        </div>

        @if($selectedEquipment)
        <!-- Info équipement -->
        <div class="dashboard-card p-6 mb-6">
            <div class="flex items-center gap-4">
                @if($selectedEquipment->images && count(json_decode($selectedEquipment->images, true)) > 0)
                    <img src="{{ asset('storage/equipment/images/' . json_decode($selectedEquipment->images, true)[0]) }}"
                         alt="{{ $selectedEquipment->name }}"
                         class="w-24 h-24 object-cover rounded-lg">
                @endif
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-white mb-1">{{ $selectedEquipment->name }}</h3>
                    <p class="text-white/70 text-sm mb-2">{{ $selectedEquipment->description }}</p>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-yellow-400 font-semibold">
                            💰 {{ $selectedEquipment->price_per_hour_credits }} crédits/heure
                        </span>
                        <span class="text-white/60">
                            📍 {{ $selectedEquipment->location }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendrier -->
        <div class="dashboard-card p-6">
            <div id="calendar"></div>
        </div>

        <!-- Légende -->
        <div class="dashboard-card p-6 mt-6">
            <h4 class="text-white font-semibold mb-4">Légende</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded" style="background-color: #f59e0b;"></div>
                    <span class="text-white/70 text-sm">En attente</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded" style="background-color: #10b981;"></div>
                    <span class="text-white/70 text-sm">Confirmée</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded" style="background-color: #ef4444;"></div>
                    <span class="text-white/70 text-sm">Indisponible</span>
                </div>
            </div>
        </div>
        @else
        <div class="dashboard-card p-12 text-center">
            <div class="w-20 h-20 rounded-full bg-white/5 flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">Sélectionnez un équipement</h3>
            <p class="text-white/60">Choisissez un équipement dans la liste ci-dessus pour voir le calendrier de réservation</p>
        </div>
        @endif

    </div>
</div>

<!-- Modal de confirmation de réservation -->
<div id="booking-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" x-data="{ show: false }" x-show="show" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="show = false"></div>

        <div class="relative bg-gradient-to-br from-gray-900/90 to-gray-800/90 backdrop-blur-xl border border-white/10 rounded-2xl p-8 max-w-lg w-full shadow-2xl">
            <h3 class="text-2xl font-bold text-white mb-4">Confirmer la réservation</h3>

            <form id="booking-form" method="POST" action="{{ route('bookings.store') }}">
                @csrf
                <input type="hidden" name="equipment_id" id="modal-equipment-id">
                <input type="hidden" name="start_datetime" id="modal-start">
                <input type="hidden" name="end_datetime" id="modal-end">

                <div class="space-y-4 mb-6">
                    <div class="bg-white/5 rounded-lg p-4">
                        <div class="text-white/60 text-sm mb-1">Période</div>
                        <div class="text-white font-semibold" id="modal-period"></div>
                    </div>

                    <div class="bg-white/5 rounded-lg p-4">
                        <div class="text-white/60 text-sm mb-1">Durée</div>
                        <div class="text-white font-semibold" id="modal-duration"></div>
                    </div>

                    <div class="bg-gradient-to-r from-yellow-500/20 to-orange-500/20 border border-yellow-500/30 rounded-lg p-4">
                        <div class="text-white/60 text-sm mb-1">Coût total</div>
                        <div class="text-yellow-400 font-bold text-xl" id="modal-cost"></div>
                    </div>

                    <div>
                        <label class="block text-white font-medium mb-2">Notes (optionnel)</label>
                        <textarea name="user_notes" rows="3" class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-white/40 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20" placeholder="Ajoutez des notes pour votre réservation..."></textarea>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="show = false" class="flex-1 px-6 py-3 rounded-lg bg-white/5 text-white hover:bg-white/10 transition-colors">
                        Annuler
                    </button>
                    <button type="submit" class="flex-1 px-6 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold hover:from-purple-700 hover:to-pink-700 transition-all">
                        Réserver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection



@push('scripts')
    @vite('resources/js/calendar.js')

    <script>
    let calendar;

    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const equipmentSelect = document.getElementById('equipment-select');
        const equipmentId = equipmentSelect ? equipmentSelect.value : null;

        if (!calendarEl || !equipmentId) {
            console.log('Calendar or equipment not selected');
            return;
        }

        // Initialiser le calendrier
        calendar = window.initBookingCalendar(calendarEl, equipmentId);

        // Rechargement lors du changement d'équipement
        if (equipmentSelect) {
            equipmentSelect.addEventListener('change', function() {
                if (this.value) {
                    window.location.href = '{{ route("bookings.calendar") }}?equipment_id=' + this.value;
                }
            });
        }

        console.log('✅ Booking calendar initialized');
    });

    // Fonction pour ouvrir le modal de réservation
    window.openBookingModal = function(start, end) {
        const equipment = @json($selectedEquipment ?? null);
        if (!equipment) return;

        const hoursDiff = (end - start) / (1000 * 60 * 60);
        const cost = Math.round(hoursDiff * equipment.price_per_hour_credits);

        document.getElementById('modal-equipment-id').value = equipment.id;
        document.getElementById('modal-start').value = start.toISOString();
        document.getElementById('modal-end').value = end.toISOString();

        document.getElementById('modal-period').textContent =
            start.toLocaleString('fr-FR') + ' → ' + end.toLocaleString('fr-FR');
        document.getElementById('modal-duration').textContent = hoursDiff + ' heure(s)';
        document.getElementById('modal-cost').textContent = cost + ' crédits';

        const modal = document.getElementById('booking-modal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.querySelector('[x-data]').__x.$data.show = true;
        }, 10);
    };
    </script>
@endpush
