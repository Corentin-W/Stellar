@extends('layouts.astral-app')

@section('title', 'Détails Réservation #' . $booking->id)

@section('content')
<div class="min-h-screen p-6">
    <div class="max-w-5xl mx-auto">

        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-3xl font-bold text-white">Réservation #{{ $booking->id }}</h1>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold text-white {{ $booking->getStatusBadgeClass() }}">
                        {{ $booking->getStatusLabel() }}
                    </span>
                </div>
                <p class="text-white/60">Créée le {{ $booking->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <a href="{{ route('admin.bookings.dashboard') }}" class="px-6 py-3 rounded-lg bg-white/5 text-white hover:bg-white/10 transition-colors">
                ← Retour
            </a>
        </div>

        @if(session('success'))
        <div class="dashboard-card p-4 mb-6 bg-green-500/20 border-green-500/50">
            <p class="text-green-400">✅ {{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="dashboard-card p-4 mb-6 bg-red-500/20 border-red-500/50">
            <p class="text-red-400">❌ {{ session('error') }}</p>
        </div>
        @endif

        <!-- Actions rapides (si pending) -->
        @if($booking->status === 'pending')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <form action="{{ route('admin.bookings.confirm', $booking) }}" method="POST">
                @csrf
                <button type="submit" class="w-full px-6 py-4 rounded-lg bg-green-500/20 text-green-400 hover:bg-green-500/30 transition-colors font-semibold text-lg flex items-center justify-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Valider la réservation
                </button>
            </form>
            <button onclick="openRejectModal()" class="w-full px-6 py-4 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors font-semibold text-lg flex items-center justify-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Rejeter la réservation
            </button>
        </div>
        @endif

        <!-- Informations principales -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Utilisateur -->
            <div class="dashboard-card p-6">
                <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Utilisateur
                </h3>
                <div class="space-y-3">
                    <div>
                        <div class="text-white/60 text-sm mb-1">Nom</div>
                        <div class="text-white font-medium">{{ $booking->user->name }}</div>
                    </div>
                    <div>
                        <div class="text-white/60 text-sm mb-1">Email</div>
                        <div class="text-white">{{ $booking->user->email }}</div>
                    </div>
                    <div>
                        <div class="text-white/60 text-sm mb-1">Solde crédits</div>
                        <div class="text-yellow-400 font-semibold">{{ number_format($booking->user->credits_balance) }} crédits</div>
                    </div>
                </div>
            </div>

            <!-- Équipement -->
            <div class="dashboard-card p-6">
                <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Équipement
                </h3>
                <div class="space-y-3">
                    <div>
                        <div class="text-white/60 text-sm mb-1">Nom</div>
                        <div class="text-white font-medium">{{ $booking->equipment->name }}</div>
                    </div>
                    <div>
                        <div class="text-white/60 text-sm mb-1">Type</div>
                        <div class="text-white capitalize">{{ $booking->equipment->type }}</div>
                    </div>
                    <div>
                        <div class="text-white/60 text-sm mb-1">Localisation</div>
                        <div class="text-white">{{ $booking->equipment->location }}</div>
                    </div>
                    <a href="{{ route('admin.equipment.show', $booking->equipment) }}" class="inline-flex items-center gap-2 text-blue-400 hover:text-blue-300 text-sm">
                        Voir l'équipement
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Dates et durée -->
            <div class="dashboard-card p-6">
                <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Planning
                </h3>
                <div class="space-y-3">
                    <div>
                        <div class="text-white/60 text-sm mb-1">Début</div>
                        <div class="text-white font-medium">{{ $booking->start_datetime->format('d/m/Y à H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-white/60 text-sm mb-1">Fin</div>
                        <div class="text-white font-medium">{{ $booking->end_datetime->format('d/m/Y à H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-white/60 text-sm mb-1">Durée</div>
                        <div class="text-white font-semibold">{{ $booking->getDurationInHours() }} heure(s)</div>
                    </div>
                    @if($booking->start_datetime->isPast())
                    <div class="px-3 py-2 bg-gray-500/20 rounded-lg text-center">
                        <span class="text-gray-400 text-sm">📅 Passée</span>
                    </div>
                    @elseif($booking->start_datetime->isToday())
                    <div class="px-3 py-2 bg-blue-500/20 rounded-lg text-center">
                        <span class="text-blue-400 text-sm">🔔 Aujourd'hui</span>
                    </div>
                    @else
                    <div class="px-3 py-2 bg-purple-500/20 rounded-lg text-center">
                        <span class="text-purple-400 text-sm">⏰ Dans {{ $booking->start_datetime->diffForHumans() }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Coût et crédits -->
        <div class="dashboard-card p-6 mb-8 bg-gradient-to-r from-yellow-500/10 to-orange-500/10 border-yellow-500/30">
            <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Historique et métadonnées
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div>
                        <div class="text-white/60 text-sm mb-1">Créée le</div>
                        <div class="text-white">{{ $booking->created_at->format('d/m/Y à H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-white/60 text-sm mb-1">Dernière modification</div>
                        <div class="text-white">{{ $booking->updated_at->format('d/m/Y à H:i') }}</div>
                    </div>
                    @if($booking->validated_at)
                    <div>
                        <div class="text-white/60 text-sm mb-1">Validée le</div>
                        <div class="text-white">{{ $booking->validated_at->format('d/m/Y à H:i') }}</div>
                    </div>
                    @endif
                </div>
                <div class="space-y-3">
                    @if($booking->validator)
                    <div>
                        <div class="text-white/60 text-sm mb-1">Validée par</div>
                        <div class="text-white">{{ $booking->validator->name }}</div>
                    </div>
                    @endif
                    <div>
                        <div class="text-white/60 text-sm mb-1">ID de réservation</div>
                        <div class="text-white font-mono">#{{ $booking->id }}</div>
                    </div>
                    <div>
                        <div class="text-white/60 text-sm mb-1">Statut actuel</div>
                        <div class="text-white">{{ $booking->getStatusLabel() }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal de rejet -->
<div id="reject-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" x-data="{ show: false }" x-show="show" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="show = false"></div>

        <div class="relative bg-gradient-to-br from-gray-900/90 to-gray-800/90 backdrop-blur-xl border border-white/10 rounded-2xl p-8 max-w-lg w-full shadow-2xl">
            <h3 class="text-2xl font-bold text-white mb-4 flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                Rejeter la réservation
            </h3>

            <form action="{{ route('admin.bookings.reject', $booking) }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label class="block text-white font-medium mb-2">Raison du rejet *</label>
                    <textarea name="rejection_reason" rows="4" required class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-white/40 focus:border-red-500 focus:ring-2 focus:ring-red-500/20" placeholder="Expliquez pourquoi vous rejetez cette réservation..."></textarea>
                    <p class="text-white/40 text-xs mt-1">Cette raison sera communiquée à l'utilisateur</p>
                </div>

                <div class="mb-6">
                    <label class="block text-white font-medium mb-2">Notes administrateur (optionnel)</label>
                    <textarea name="admin_notes" rows="3" class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-white/40 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20" placeholder="Notes internes (non visibles par l'utilisateur)..."></textarea>
                </div>

                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-yellow-400 text-sm font-medium mb-1">Conséquences du rejet :</p>
                            <ul class="text-yellow-400/80 text-xs space-y-1">
                                <li>• {{ number_format($booking->credits_cost) }} crédits seront automatiquement remboursés</li>
                                <li>• L'utilisateur recevra une notification</li>
                                <li>• Cette action est irréversible</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="show = false" class="flex-1 px-6 py-3 rounded-lg bg-white/5 text-white hover:bg-white/10 transition-colors">
                        Annuler
                    </button>
                    <button type="submit" class="flex-1 px-6 py-3 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-all shadow-lg">
                        Confirmer le rejet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openRejectModal() {
    const modal = document.getElementById('reject-modal');
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.querySelector('[x-data]').__x.$data.show = true;
    }, 10);
}

// Auto-complétion des raisons courantes
document.addEventListener('DOMContentLoaded', function() {
    const rejectForm = document.querySelector('#reject-modal form');
    if (rejectForm) {
        const reasonTextarea = rejectForm.querySelector('textarea[name="rejection_reason"]');
        if (reasonTextarea) {
            // Suggestions de raisons courantes
            const commonReasons = [
                'Équipement non disponible à ces dates',
                'Maintenance planifiée sur cette période',
                'Conditions météo défavorables prévues',
                'Conflit avec une autre réservation prioritaire',
                'Équipement actuellement en panne',
                'Demande hors des horaires d\'ouverture'
            ];

            // Créer un menu de suggestions
            const suggestionsDiv = document.createElement('div');
            suggestionsDiv.className = 'mt-2 space-y-1';
            suggestionsDiv.innerHTML = '<div class="text-white/60 text-xs mb-2">Suggestions :</div>';

            commonReasons.forEach(reason => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'text-xs px-2 py-1 rounded bg-white/5 hover:bg-white/10 text-white/70 hover:text-white transition-colors mr-2 mb-1';
                btn.textContent = reason;
                btn.onclick = () => {
                    reasonTextarea.value = reason;
                    reasonTextarea.focus();
                };
                suggestionsDiv.appendChild(btn);
            });

            reasonTextarea.parentNode.appendChild(suggestionsDiv);
        }
    }
});
</script>
@endpush 
