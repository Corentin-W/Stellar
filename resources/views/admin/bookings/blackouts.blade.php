@extends('layouts.astral-app')

@section('title', 'Gestion des Blocages')

@section('content')
<div class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">🚫 Gestion des Blocages</h1>
                <p class="text-white/60">Créez des périodes d'indisponibilité (maintenance, météo...)</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.bookings.calendar') }}" class="px-6 py-3 rounded-lg bg-white/5 text-white hover:bg-white/10 transition-colors">
                    📅 Calendrier
                </a>
                <a href="{{ route('admin.bookings.dashboard') }}" class="px-6 py-3 rounded-lg bg-white/5 text-white hover:bg-white/10 transition-colors">
                    ← Retour
                </a>
            </div>
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

        <!-- Créer un blocage -->
        <div class="dashboard-card p-6 mb-8">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Créer un nouveau blocage
            </h2>

            <form action="{{ route('admin.bookings.blackouts.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Équipement -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            Équipement concerné
                            <span class="text-white/40 text-sm font-normal ml-1">(optionnel)</span>
                        </label>
                        <select name="equipment_id" class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                            <option value="">Tous les équipements</option>
                            @foreach($equipments as $equipment)
                                <option value="{{ $equipment->id }}">{{ $equipment->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-white/40 text-xs mt-1">Laissez vide pour bloquer tous les équipements</p>
                    </div>

                    <!-- Raison -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            Raison du blocage *
                        </label>
                        <input type="text" name="reason" required maxlength="255"
                               placeholder="Ex: Maintenance planifiée, Météo défavorable..."
                               class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-white/40 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                    </div>

                    <!-- Date et heure de début -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            Début du blocage *
                        </label>
                        <input type="datetime-local" name="start_datetime" required
                               min="{{ now()->format('Y-m-d\TH:i') }}"
                               class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                    </div>

                    <!-- Date et heure de fin -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            Fin du blocage *
                        </label>
                        <input type="datetime-local" name="end_datetime" required
                               min="{{ now()->format('Y-m-d\TH:i') }}"
                               class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-white font-medium mb-2">
                        Description détaillée (optionnel)
                    </label>
                    <textarea name="description" rows="3" maxlength="1000"
                              placeholder="Ajoutez des détails sur ce blocage..."
                              class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-white/40 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"></textarea>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <button type="reset" class="px-6 py-3 rounded-lg bg-white/5 text-white hover:bg-white/10 transition-colors">
                        Réinitialiser
                    </button>
                    <button type="submit" class="px-6 py-3 rounded-lg bg-gradient-to-r from-red-600 to-pink-600 text-white font-semibold hover:from-red-700 hover:to-pink-700 transition-all shadow-lg">
                        🚫 Créer le blocage
                    </button>
                </div>
            </form>
        </div>

        <!-- Liste des blocages actifs -->
        <div class="dashboard-card p-6">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Blocages actifs et à venir
                </span>
                <span class="text-sm font-normal text-white/60">
                    {{ $blackouts->total() }} blocage(s)
                </span>
            </h2>

            @if($blackouts->count() > 0)
            <div class="space-y-4">
                @foreach($blackouts as $blackout)
                <div class="p-6 bg-red-500/10 border border-red-500/30 rounded-lg hover:bg-red-500/20 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <!-- Info principale -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-lg bg-red-500/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-white">{{ $blackout->reason }}</h3>
                                    <p class="text-red-400 text-sm">
                                        @if($blackout->equipment)
                                            {{ $blackout->equipment->name }}
                                        @else
                                            <span class="font-semibold">Tous les équipements</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                                <div class="flex items-center gap-2 text-white/70">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm">{{ $blackout->start_datetime->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-white/70">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                    <span class="text-sm">{{ $blackout->end_datetime->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-white/70">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm">
                                        {{ $blackout->start_datetime->diffInHours($blackout->end_datetime) }}h
                                    </span>
                                </div>
                            </div>

                            @if($blackout->description)
                            <div class="p-3 bg-white/5 rounded-lg">
                                <div class="text-white/60 text-xs mb-1">Description</div>
                                <div class="text-white text-sm">{{ $blackout->description }}</div>
                            </div>
                            @endif

                            <div class="mt-3 text-xs text-white/40">
                                Créé par {{ $blackout->creator->name }} le {{ $blackout->created_at->format('d/m/Y à H:i') }}
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-2">
                            @if($blackout->start_datetime->isFuture())
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-400 text-center">
                                À venir
                            </span>
                            @else
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-400 text-center">
                                En cours
                            </span>
                            @endif

                            <form action="{{ route('admin.bookings.blackouts.destroy', $blackout) }}" method="POST"
                                  onsubmit="return confirm('Supprimer ce blocage ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-4 py-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors text-sm font-medium">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($blackouts->hasPages())
            <div class="mt-6">
                {{ $blackouts->links() }}
            </div>
            @endif

            @else
            <div class="text-center py-12">
                <div class="w-20 h-20 rounded-full bg-white/5 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Aucun blocage actif</h3>
                <p class="text-white/60">Créez un blocage pour rendre des équipements indisponibles temporairement</p>
            </div>
            @endif
        </div>

        <!-- Informations -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <!-- Guide d'utilisation -->
            <div class="dashboard-card p-6 bg-blue-500/10 border-blue-500/30">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-lg bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-blue-400 font-semibold mb-2">ℹ️ Guide d'utilisation</h4>
                        <ul class="text-white/70 text-sm space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-400 mt-1">•</span>
                                <span>Créez un blocage pour rendre un équipement indisponible pendant une période donnée</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-400 mt-1">•</span>
                                <span>Laissez "Équipement" vide pour bloquer tous les équipements en même temps</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-400 mt-1">•</span>
                                <span>Les utilisateurs ne pourront pas réserver pendant les périodes bloquées</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-400 mt-1">•</span>
                                <span>Les blocages apparaissent en rouge sur le calendrier</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Exemples de blocages -->
            <div class="dashboard-card p-6 bg-purple-500/10 border-purple-500/30">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-lg bg-purple-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-purple-400 font-semibold mb-2">💡 Exemples de blocages</h4>
                        <ul class="text-white/70 text-sm space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-purple-400 mt-1">•</span>
                                <span><strong class="text-white">Maintenance :</strong> "Maintenance du télescope principal"</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-purple-400 mt-1">•</span>
                                <span><strong class="text-white">Météo :</strong> "Météo défavorable - Nuages prévus"</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-purple-400 mt-1">•</span>
                                <span><strong class="text-white">Événement :</strong> "Soirée portes ouvertes publique"</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-purple-400 mt-1">•</span>
                                <span><strong class="text-white">Technique :</strong> "Mise à jour du système de contrôle"</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    /* Animation pour les nouveaux blocages */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dashboard-card {
        animation: slideIn 0.3s ease-out;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation des dates
    const startDateInput = document.querySelector('input[name="start_datetime"]');
    const endDateInput = document.querySelector('input[name="end_datetime"]');

    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            // Mettre à jour le minimum de la date de fin
            endDateInput.min = this.value;

            // Si la date de fin est avant la date de début, la réinitialiser
            if (endDateInput.value && endDateInput.value < this.value) {
                endDateInput.value = '';
            }
        });

        // Valider avant soumission
        const form = startDateInput.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const start = new Date(startDateInput.value);
                const end = new Date(endDateInput.value);

                if (end <= start) {
                    e.preventDefault();
                    alert('La date de fin doit être après la date de début');
                    return false;
                }

                // Confirmer si blocage global
                const equipmentSelect = form.querySelector('select[name="equipment_id"]');
                if (equipmentSelect && !equipmentSelect.value) {
                    if (!confirm('⚠️ Vous allez bloquer TOUS les équipements. Confirmez-vous ?')) {
                        e.preventDefault();
                        return false;
                    }
                }

                return true;
            });
        }
    }

    // Auto-complétion de raisons courantes
    const reasonInput = document.querySelector('input[name="reason"]');
    if (reasonInput) {
        const commonReasons = [
            'Maintenance planifiée',
            'Météo défavorable',
            'Réparation en cours',
            'Événement spécial',
            'Mise à jour système',
            'Nettoyage et calibration'
        ];

        // Créer une datalist pour l'auto-complétion
        const datalist = document.createElement('datalist');
        datalist.id = 'reason-suggestions';
        commonReasons.forEach(reason => {
            const option = document.createElement('option');
            option.value = reason;
            datalist.appendChild(option);
        });
        reasonInput.setAttribute('list', 'reason-suggestions');
        reasonInput.parentNode.appendChild(datalist);
    }

    // Confirmation de suppression améliorée
    document.querySelectorAll('form[onsubmit*="confirm"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const blackoutCard = this.closest('.p-6');
            const reason = blackoutCard.querySelector('h3').textContent;

            if (confirm(`🚫 Supprimer le blocage "${reason}" ?\n\nCette action est irréversible.`)) {
                this.submit();
            }
        });
    });

    // Calcul automatique de la durée
    if (startDateInput && endDateInput) {
        const updateDuration = () => {
            if (startDateInput.value && endDateInput.value) {
                const start = new Date(startDateInput.value);
                const end = new Date(endDateInput.value);
                const hours = Math.abs(end - start) / 36e5; // Différence en heures

                if (hours > 0) {
                    const days = Math.floor(hours / 24);
                    const remainingHours = Math.floor(hours % 24);

                    let durationText = '';
                    if (days > 0) durationText += `${days}j `;
                    if (remainingHours > 0) durationText += `${remainingHours}h`;

                    // Afficher la durée (créer un élément si nécessaire)
                    let durationDisplay = document.getElementById('duration-display');
                    if (!durationDisplay) {
                        durationDisplay = document.createElement('div');
                        durationDisplay.id = 'duration-display';
                        durationDisplay.className = 'mt-2 text-sm text-purple-400';
                        endDateInput.parentNode.appendChild(durationDisplay);
                    }
                    durationDisplay.textContent = `⏱️ Durée: ${durationText.trim()}`;
                }
            }
        };

        startDateInput.addEventListener('change', updateDuration);
        endDateInput.addEventListener('change', updateDuration);
    }

    console.log('✅ Blackouts management initialized');
});
</script>
@endpush
