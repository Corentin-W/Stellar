@extends('layouts.astral-app')

@section('title', 'Mes Réservations')

@section('content')
<div class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">🎫 Mes Réservations</h1>
                <p class="text-white/60">Gérez vos réservations d'équipement</p>
            </div>
            <a href="{{ route('bookings.calendar') }}" class="px-6 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold hover:from-purple-700 hover:to-pink-700 transition-all">
                ➕ Nouvelle réservation
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

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="dashboard-card p-6">
                <div class="text-yellow-400 text-sm mb-2">⏳ En attente</div>
                <div class="text-3xl font-bold text-white">{{ $bookings->where('status', 'pending')->count() }}</div>
            </div>
            <div class="dashboard-card p-6">
                <div class="text-green-400 text-sm mb-2">✅ Confirmées</div>
                <div class="text-3xl font-bold text-white">{{ $bookings->where('status', 'confirmed')->count() }}</div>
            </div>
            <div class="dashboard-card p-6">
                <div class="text-blue-400 text-sm mb-2">📅 À venir</div>
                <div class="text-3xl font-bold text-white">
                    {{ $bookings->where('start_datetime', '>', now())->whereIn('status', ['pending', 'confirmed'])->count() }}
                </div>
            </div>
            <div class="dashboard-card p-6">
                <div class="text-purple-400 text-sm mb-2">💰 Crédits utilisés</div>
                <div class="text-3xl font-bold text-white">{{ number_format($bookings->where('status', '!=', 'cancelled')->sum('credits_cost')) }}</div>
            </div>
        </div>

        <!-- Liste des réservations -->
        <div class="space-y-4">
            @forelse($bookings as $booking)
            <div class="dashboard-card p-6 hover:scale-[1.01] transition-transform">
                <div class="flex items-start justify-between gap-4">
                    <!-- Info principale -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <h3 class="text-xl font-bold text-white">{{ $booking->equipment->name }}</h3>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold text-white {{ $booking->getStatusBadgeClass() }}">
                                {{ $booking->getStatusLabel() }}
                            </span>
                            @if($booking->start_datetime->isPast() && $booking->status === 'confirmed')
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-500 text-white">
                                Passée
                            </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div class="flex items-center gap-2 text-white/70">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ $booking->start_datetime->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-white/70">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ $booking->getDurationInHours() }} heure(s)</span>
                            </div>
                            <div class="flex items-center gap-2 text-white/70">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ $booking->equipment->location }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-yellow-400 font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                                <span>{{ number_format($booking->credits_cost) }} crédits</span>
                            </div>
                        </div>

                        @if($booking->user_notes)
                        <div class="mt-3 p-3 bg-white/5 rounded-lg">
                            <div class="text-white/60 text-xs mb-1">Vos notes</div>
                            <div class="text-white text-sm">{{ $booking->user_notes }}</div>
                        </div>
                        @endif

                        @if($booking->admin_notes)
                        <div class="mt-3 p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                            <div class="text-blue-400 text-xs mb-1">📝 Note de l'administrateur</div>
                            <div class="text-white text-sm">{{ $booking->admin_notes }}</div>
                        </div>
                        @endif

                        @if($booking->rejection_reason)
                        <div class="mt-3 p-3 bg-red-500/10 border border-red-500/30 rounded-lg">
                            <div class="text-red-400 text-xs mb-1">❌ Raison du rejet</div>
                            <div class="text-white text-sm">{{ $booking->rejection_reason }}</div>
                        </div>
                        @endif

                        @if($booking->cancellation_reason)
                        <div class="mt-3 p-3 bg-gray-500/10 border border-gray-500/30 rounded-lg">
                            <div class="text-gray-400 text-xs mb-1">🚫 Raison de l'annulation</div>
                            <div class="text-white text-sm">{{ $booking->cancellation_reason }}</div>
                        </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-2">
                        @php
                            $accessState = $booking->getAccessState();
                            $startLabel = $booking->start_datetime->copy()->locale(app()->getLocale())->isoFormat('ddd D MMM HH:mm');
                            $endLabel = $booking->end_datetime->copy()->locale(app()->getLocale())->isoFormat('ddd D MMM HH:mm');
                        @endphp

                        @php
                            $hasPlan = $booking->hasTargetPlan();
                        @endphp

                        @if(in_array($booking->status, ['confirmed', 'pending']))
                        <a href="{{ route('bookings.prepare', ['locale' => app()->getLocale(), 'booking' => $booking]) }}"
                           class="px-4 py-2 rounded-lg bg-purple-500/20 text-purple-200 hover:bg-purple-500/30 transition-colors text-sm font-medium text-center">
                            {{ $hasPlan ? 'Modifier la préparation' : 'Préparer ma session' }}
                        </a>
                        @endif

                        @if($booking->canBeCancelled())
                        <button onclick="openCancelModal(this)"
                                data-cancel-action="{{ route('bookings.cancel', ['locale' => app()->getLocale(), 'booking' => $booking]) }}"
                                class="px-4 py-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors text-sm font-medium">
                            Annuler
                        </button>
                        @endif

                        @if($accessState === 'active')
                        <a href="{{ route('bookings.access', ['locale' => app()->getLocale(), 'booking' => $booking]) }}"
                           class="px-4 py-2 rounded-lg bg-green-500/20 text-green-200 hover:bg-green-500/30 transition-colors text-sm font-medium text-center">
                            Accéder au matériel
                        </a>
                        <div class="px-4 py-2 rounded-lg bg-white/5 text-white/70 text-xs text-center">
                            Session en cours jusqu'au {{ $endLabel }}
                        </div>
                        @elseif($accessState === 'upcoming')
                        <a href="{{ route('bookings.access', ['locale' => app()->getLocale(), 'booking' => $booking]) }}"
                           class="px-4 py-2 rounded-lg bg-blue-500/20 text-blue-200 hover:bg-blue-500/30 transition-colors text-sm font-medium text-center">
                            Page d'accès
                        </a>
                        <div class="px-4 py-2 rounded-lg bg-white/5 text-white/70 text-xs text-center">
                            Débute le {{ $startLabel }}
                        </div>
                        @elseif($accessState === 'pending')
                        <div class="px-4 py-2 rounded-lg bg-yellow-500/20 text-yellow-200 text-sm text-center">
                            ⏳ En attente de validation
                        </div>
                        @elseif($accessState === 'finished')
                        <div class="px-4 py-2 rounded-lg bg-blue-500/20 text-blue-200 text-sm text-center">
                            ✅ Session terminée
                        </div>
                        <a href="{{ route('bookings.access', ['locale' => app()->getLocale(), 'booking' => $booking]) }}"
                           class="px-4 py-2 rounded-lg bg-white/5 text-white/60 hover:text-white/80 transition-colors text-xs text-center">
                            Revoir la page d'accès
                        </a>
                        @elseif($accessState === 'cancelled')
                        <div class="px-4 py-2 rounded-lg bg-gray-500/20 text-gray-300 text-sm text-center">
                            Réservation annulée
                        </div>
                        @elseif($accessState === 'blocked')
                        <div class="px-4 py-2 rounded-lg bg-gray-500/20 text-gray-300 text-sm text-center">
                            Accès indisponible
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="dashboard-card p-12 text-center">
                <div class="w-20 h-20 rounded-full bg-white/5 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Aucune réservation</h3>
                <p class="text-white/60 mb-4">Vous n'avez pas encore de réservation</p>
                <a href="{{ route('bookings.calendar') }}" class="inline-flex items-center px-6 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold hover:from-purple-700 hover:to-pink-700 transition-all">
                    Créer ma première réservation
                </a>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($bookings->hasPages())
        <div class="mt-8">
            {{ $bookings->links() }}
        </div>
        @endif

    </div>
</div>

<!-- Modal d'annulation -->
<div id="cancel-modal"
     class="hidden fixed inset-0 z-50 overflow-y-auto"
     x-data="{ show: false }"
     x-show="show"
     x-cloak
     x-effect="show ? $el.classList.remove('hidden') : $el.classList.add('hidden')"
     x-on:cancel-modal-open.window="show = true"
     x-on:cancel-modal-close.window="show = false"
>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="show = false"></div>

        <div class="relative bg-gradient-to-br from-gray-900/90 to-gray-800/90 backdrop-blur-xl border border-white/10 rounded-2xl p-8 max-w-lg w-full shadow-2xl">
            <h3 class="text-2xl font-bold text-white mb-4">⚠️ Annuler la réservation</h3>

            <form id="cancel-form" method="POST">
                @csrf
                @method('POST')
                <input type="hidden" name="expects_json" value="1">

                <div class="mb-6">
                    <label class="block text-white font-medium mb-2">Raison de l'annulation *</label>
                    <textarea name="cancellation_reason" rows="4" required class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-white/40 focus:border-red-500 focus:ring-2 focus:ring-red-500/20" placeholder="Expliquez pourquoi vous annulez cette réservation..."></textarea>
                </div>

                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 mb-6">
                    <p class="text-yellow-400 text-sm">
                        ℹ️ Vos crédits seront automatiquement remboursés après l'annulation.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="show = false" class="flex-1 px-6 py-3 rounded-lg bg-white/5 text-white hover:bg-white/10 transition-colors">
                        Retour
                    </button>
                    <button type="submit" id="cancel-submit"
                            class="flex-1 px-6 py-3 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-all">
                        Confirmer l'annulation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openCancelModal(trigger) {
    const modal = document.getElementById('cancel-modal');
    const form = document.getElementById('cancel-form');
    const submitBtn = document.getElementById('cancel-submit');

    if (!modal || !form || !submitBtn) {
        console.warn('[Bookings] Impossible d\'ouvrir le modal d\'annulation (éléments manquants).');
        return;
    }

    const actionUrl = typeof trigger === 'string'
        ? trigger
        : trigger?.dataset?.cancelAction;

    if (!actionUrl) {
        console.warn('[Bookings] URL d\'annulation indisponible.');
        return;
    }

    form.action = actionUrl;
    submitBtn.disabled = false;
    submitBtn.dataset.originalText = submitBtn.textContent;
    submitBtn.textContent = 'Confirmer l\'annulation';
    form.dataset.redirectRefresh = window.location.href;

    modal.classList.remove('hidden');
    window.dispatchEvent(new CustomEvent('cancel-modal-open'));
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('cancel-form');
    const submitBtn = document.getElementById('cancel-submit');

    if (!form || !submitBtn) {
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        submitBtn.disabled = true;
        submitBtn.textContent = 'Annulation en cours...';

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: formData
            });

            let payload = {};
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                payload = await response.json();
            }

            if (!response.ok) {
                throw new Error(payload.message || 'Réponse inattendue du serveur');
            }

            window.dispatchEvent(new CustomEvent('cancel-modal-close'));
            form.reset();

            if (typeof window.showNotification === 'function') {
                window.showNotification('Réservation annulée', payload.message || 'Crédits remboursés.', 'success', 4000);
            }

            const redirectUrl = payload.redirect
                || form.dataset.redirectRefresh
                || formData.get('redirect_url');

            const targetUrl = redirectUrl || `${window.location.pathname}?refresh=${Date.now()}`;

            setTimeout(() => {
                window.location.href = targetUrl;
            }, 800);

        } catch (error) {
            console.error('[Bookings] Erreur lors de l\'annulation', error);

            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.dataset.originalText || 'Confirmer l\'annulation';

            if (typeof window.showNotification === 'function') {
                window.showNotification('Erreur', 'Impossible d\'annuler la réservation.', 'error', 4500);
            }
        }
    });
});
</script>
@endpush
