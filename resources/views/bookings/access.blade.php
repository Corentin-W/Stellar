@extends('layouts.astral-app')

@section('title', 'Accès au matériel')

@section('content')
@php
    $locale = app()->getLocale();
    $startDisplay = $start->copy()->locale($locale)->isoFormat('dddd D MMMM YYYY HH:mm');
    $endDisplay = $end->copy()->locale($locale)->isoFormat('dddd D MMMM YYYY HH:mm');
@endphp
<div class="min-h-screen p-6">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-wider text-white/40 mb-1">Accès équipement</p>
                <h1 class="text-3xl md:text-4xl font-bold text-white flex items-center gap-3">
                    <span>🔭 {{ $equipment->name }}</span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold text-white {{ $booking->getStatusBadgeClass() }}">
                        {{ $booking->getStatusLabel() }}
                    </span>
                </h1>
            </div>
            <a href="{{ route('bookings.my-bookings', ['locale' => app()->getLocale()]) }}"
               class="px-5 py-3 rounded-lg bg-white/5 text-white hover:bg-white/10 transition-colors">
                ⬅️ Retour à mes réservations
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="dashboard-card p-5">
                <p class="text-white/60 text-sm mb-2">Début</p>
                <p class="text-xl font-semibold text-white">{{ $startDisplay }}</p>
            </div>
            <div class="dashboard-card p-5">
                <p class="text-white/60 text-sm mb-2">Fin</p>
                <p class="text-xl font-semibold text-white">{{ $endDisplay }}</p>
            </div>
            <div class="dashboard-card p-5">
                <p class="text-white/60 text-sm mb-2">Fuseau horaire</p>
                <p class="text-xl font-semibold text-white">{{ $timezoneLabel }}</p>
            </div>
        </div>

        @if($state === 'pending')
            <div class="dashboard-card p-6 border border-yellow-500/40 bg-yellow-500/10 text-yellow-200">
                <h2 class="text-2xl font-semibold mb-2">⏳ Réservation en attente</h2>
                <p>Un administrateur doit confirmer votre réservation avant que l'accès soit disponible. Vous recevrez une notification dès validation.</p>
            </div>
        @elseif($state === 'cancelled')
            <div class="dashboard-card p-6 border border-red-500/40 bg-red-500/10 text-red-200">
                <h2 class="text-2xl font-semibold mb-2">❌ Réservation annulée</h2>
                <p>Cette session a été annulée. Aucun accès n'est possible.</p>
            </div>
        @elseif($state === 'finished')
            <div class="dashboard-card p-6 border border-blue-500/40 bg-blue-500/10 text-blue-200">
                <h2 class="text-2xl font-semibold mb-2">✅ Session terminée</h2>
                <p>La fenêtre de réservation est clôturée. Nous espérons que votre session d'observation s'est bien passée !</p>
            </div>
        @elseif($state === 'upcoming')
            <div class="dashboard-card p-6 border border-purple-500/40 bg-purple-500/10 text-purple-100">
                <h2 class="text-2xl font-semibold mb-4">🔒 Accès verrouillé pour le moment</h2>
                <p class="mb-4">La fenêtre d'accès s'ouvrira automatiquement lorsque la réservation commencera.</p>
                <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                    <div class="text-sm uppercase tracking-wide text-purple-200">Déverrouillage dans</div>
                    <div id="access-countdown" data-seconds="{{ $secondsToStart }}" data-refresh="true"
                         class="text-4xl font-bold">--:--:--</div>
                </div>
                <p class="mt-4 text-sm text-purple-200/80">Cette page se rechargera automatiquement à l'heure prévue.</p>
            </div>
        @elseif($state === 'active')
            <div class="dashboard-card p-6 border border-green-500/40 bg-green-500/10 text-green-100 space-y-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold">🟢 Accès au matériel disponible</h2>
                        <p class="text-green-200/80">Vous pouvez maintenant contrôler l'équipement réservé.</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm uppercase tracking-wide text-green-200">Temps restant</p>
                        <p id="access-session-timer" data-seconds="{{ $secondsToEnd }}" class="text-3xl font-bold">--:--:--</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-white/90">
                    <div class="bg-white/5 rounded-xl p-5 border border-white/10">
                        <h3 class="text-lg font-semibold mb-3">Étapes recommandées</h3>
                        <ul class="space-y-2 text-sm text-white/70">
                            <li>1. Ouvrez l'application de contrôle distante dédiée à l'équipement.</li>
                            <li>2. Connectez-vous avec vos identifiants habituels.</li>
                            <li>3. Vérifiez l'état du matériel avant toute manipulation.</li>
                            <li>4. Respectez la durée réservée afin de libérer la ressource à l'heure.</li>
                        </ul>
                    </div>
                    <div class="bg-white/5 rounded-xl p-5 border border-white/10">
                        <h3 class="text-lg font-semibold mb-3">Informations utiles</h3>
                        <p class="text-sm text-white/70 mb-3">{{ $equipment->description ?: "Aucune description détaillée pour cet équipement." }}</p>
                        <div class="text-sm text-white/60">
                            @if($equipment->location)
                                <p class="mb-1">📍 Localisation : {{ $equipment->location }}</p>
                            @endif
                            <p>💳 Coût : {{ $booking->credits_cost }} crédits</p>
                        </div>
                    </div>
                </div>

                <div class="bg-green-500/20 border border-green-500/40 rounded-xl p-4 text-sm text-green-100">
                    Besoin d'assistance ? Contactez l'équipe support depuis le centre d'aide. Pensez à noter toute anomalie rencontrée pendant la session.
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const formatDuration = (totalSeconds) => {
        const total = Math.max(0, Math.floor(totalSeconds));
        const hours = String(Math.floor(total / 3600)).padStart(2, '0');
        const minutes = String(Math.floor((total % 3600) / 60)).padStart(2, '0');
        const seconds = String(total % 60).padStart(2, '0');
        return `${hours}:${minutes}:${seconds}`;
    };

    const tickCountdown = (el, refreshOnFinish = false) => {
        if (!el) {
            return;
        }

        let remaining = parseInt(el.dataset.seconds, 10);
        if (Number.isNaN(remaining)) {
            remaining = 0;
        }

        const refresh = refreshOnFinish === true;
        el.textContent = formatDuration(remaining);

        const interval = setInterval(() => {
            remaining -= 1;
            if (remaining <= 0) {
                clearInterval(interval);
                el.textContent = formatDuration(0);
                if (refresh) {
                    setTimeout(() => window.location.reload(), 1200);
                }
                return;
            }
            el.textContent = formatDuration(remaining);
        }, 1000);
    };

    document.addEventListener('DOMContentLoaded', () => {
        const countdownEl = document.getElementById('access-countdown');
        if (countdownEl) {
            tickCountdown(countdownEl, countdownEl.dataset.refresh === 'true');
        }

        const sessionTimerEl = document.getElementById('access-session-timer');
        if (sessionTimerEl) {
            tickCountdown(sessionTimerEl, false);
        }
    });
})();
</script>
@endpush
