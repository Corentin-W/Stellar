@extends('layouts.astral-app')

@section('title', 'Accès au matériel')

@section('content')
@php
    $locale = app()->getLocale();
    $startDisplay = $start->copy()->locale($locale)->isoFormat('dddd D MMMM YYYY HH:mm');
    $endDisplay = $end->copy()->locale($locale)->isoFormat('dddd D MMMM YYYY HH:mm');
    $controlConfig = [
        'statusUrl' => $controlRoutes['status'] ?? null,
        'abortUrl' => $controlRoutes['abort'] ?? null,
        'toggleUrl' => $controlRoutes['toggle'] ?? null,
        'previewUrl' => $controlRoutes['preview'] ?? null,
        'webcamUrl' => $controlRoutes['webcam'] ?? null,
        'state' => $state,
        'targetGuid' => $booking->voyager_target_guid ?? null,
        'setGuid' => $booking->voyager_set_guid ?? null,
    ];
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

        @if(in_array($state, ['active', 'upcoming'], true))
            <div
                x-cloak
                x-data='bookingControlPanel(@json($controlConfig))'
                x-init="init()"
                class="dashboard-card p-6 border border-white/10 bg-white/5 text-white/80 space-y-6"
            >
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-white">Contrôle du matériel</h2>
                        <p class="text-sm text-white/60">
                            Suivi temps réel des équipements connectés à Voyager.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition"
                            @click="fetchStatus"
                            :disabled="loading || actionLoading"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Rafraîchir</span>
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg bg-red-500/20 px-4 py-2 text-sm font-semibold text-red-200 hover:bg-red-500/30 transition disabled:opacity-60 disabled:cursor-not-allowed"
                            @click="abortSession"
                            :disabled="!canAbort || actionLoading"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728"/>
                            </svg>
                            <span>Arrêter la session</span>
                        </button>
                    </div>
                </div>

                <div x-show="loading" class="rounded-lg border border-white/10 bg-white/5 p-4 text-sm text-white/60">
                    Chargement des données Voyager…
                </div>

                <template x-if="error">
                    <div class="rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-100" x-text="error"></div>
                </template>

                <template x-if="control">
                    <div class="space-y-6">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div class="text-sm text-white/60">
                                Dernière mise à jour :
                                <span class="font-semibold text-white" x-text="lastUpdatedLabel || '—'"></span>
                            </div>
                            <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold"
                                 :class="control.source === 'mock'
                                    ? 'bg-yellow-500/30 text-yellow-100'
                                    : 'bg-green-500/30 text-green-100'">
                                <span class="w-2 h-2 rounded-full"
                                      :class="control.source === 'mock' ? 'bg-yellow-300 animate-pulse' : 'bg-green-300'"></span>
                                <span x-text="control.source === 'mock' ? 'Mode démonstration' : 'Connexion proxy active'"></span>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-3">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-white/60">Équipements</h3>
                                <template x-for="equipment in (control.data?.equipment || [])" :key="equipment.key">
                                    <div class="flex items-center justify-between rounded-lg border border-white/10 bg-white/5 p-3">
                                        <div>
                                            <p class="font-semibold text-white" x-text="equipment.label"></p>
                                            <p class="text-xs text-white/60" x-text="equipment.status"></p>
                                            <p class="text-[0.7rem] text-white/40" x-show="equipment.detail" x-text="equipment.detail"></p>
                                        </div>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full"
                                              :class="equipment.connected ? 'bg-green-500/25 text-green-100' : 'bg-gray-500/25 text-gray-200'">
                                            <span x-text="equipment.connected ? 'Connecté' : 'Hors ligne'"></span>
                                        </span>
                                    </div>
                                </template>
                            </div>
                            <div class="space-y-3">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-white/60">Séquence</h3>
                                <div class="rounded-lg border border-white/10 bg-white/5 p-4 space-y-4">
                                    <div class="flex items-center justify-between text-sm text-white/70">
                                        <span>Cible sélectionnée</span>
                                        <span class="font-semibold text-white" x-text="control.data?.target_name || '—'"></span>
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between text-[0.7rem] uppercase tracking-wide text-white/50 mb-1">
                                            <span x-text="`Progression ${sequenceProgress}%`"></span>
                                            <span x-text="sequenceLabel"></span>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-white/10">
                                            <div class="h-2 bg-gradient-to-r from-purple-500 to-pink-500 transition-all duration-500"
                                                 :style="`width: ${sequenceProgress}%;`"></div>
                                        </div>
                                    </div>
                                    <template x-if="control.data?.exposure">
                                        <div class="rounded-lg border border-white/10 bg-white/5 p-3 text-xs text-white/70 space-y-1">
                                            <div class="flex items-center justify-between">
                                                <span>Exposition en cours</span>
                                                <span class="font-semibold text-white" x-text="`Filtre ${control.data.exposure.filter}`"></span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span x-text="`Écoulé : ${formatSeconds(control.data.exposure.elapsed)}`"></span>
                                                <span x-text="`Restant : ${formatSeconds(control.data.exposure.remaining)}`"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <div class="text-xs text-white/60">
                                        <span x-text="`Prises : ${control.data?.sequence?.shots_done ?? 0}/${control.data?.sequence?.shots_total ?? 0}`"></span>
                                    </div>
                                </div>
                                <template x-if="supportsToggle">
                                    <button
                                        type="button"
                                        class="w-full rounded-lg px-4 py-2 text-sm font-medium transition disabled:opacity-60 disabled:cursor-not-allowed"
                                        :class="targetEnabled ? 'bg-white/10 text-white hover:bg-white/20' : 'bg-purple-500/20 text-purple-200 hover:bg-purple-500/30'"
                                        @click="toggleTarget"
                                        :disabled="actionLoading"
                                    >
                                        <span x-text="targetEnabled ? 'Désactiver la cible' : 'Activer la cible'"></span>
                                    </button>
                                </template>
                            </div>
                            <div class="space-y-3 md:col-span-2">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-white/60">Caméra</h3>
                                <div class="rounded-lg border border-white/10 bg-white/5 p-4 space-y-4">
                                    <div class="relative aspect-video w-full overflow-hidden rounded-lg border border-white/10 bg-black/40">
                                        <template x-if="previewImage">
                                            <img :src="previewImage" alt="Aperçu caméra" class="h-full w-full object-cover">
                                        </template>
                                        <template x-if="!previewImage">
                                            <div class="flex h-full w-full items-center justify-center px-6 text-center text-sm text-white/50">
                                                Aucun aperçu disponible pour le moment.
                                            </div>
                                        </template>
                                        <div x-show="previewLoading" class="absolute inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm">
                                            <svg class="h-10 w-10 animate-spin text-white" viewBox="0 0 24 24" fill="none">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-white/80 transition hover:text-white hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-60"
                                            @click="fetchPreview"
                                            :disabled="previewLoading || !previewUrl"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            <span>Actualiser l'aperçu</span>
                                        </button>
                                        <template x-if="hasWebcamLink()">
                                            <a
                                                :href="webcamUrl"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-2 rounded-lg bg-blue-500/20 px-4 py-2 text-sm font-semibold text-blue-200 transition hover:bg-blue-500/30"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 7h6a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z"/>
                                                </svg>
                                                <span>Webcam live</span>
                                            </a>
                                        </template>
                                    </div>
                                    <div class="text-xs text-white/60"
                                         x-text="previewTimestampLabel() ? `Dernier rafraîchissement : ${previewTimestampLabel()}` : 'En attente du premier aperçu...'"></div>
                                    <template x-if="previewError">
                                        <div class="rounded-lg border border-yellow-500/40 bg-yellow-500/10 p-3 text-xs text-yellow-100" x-text="previewError"></div>
                                    </template>
                                    <template x-if="!previewUrl">
                                        <div class="rounded-lg border border-white/10 bg-white/5 p-3 text-xs text-white/60">
                                            Service de prévisualisation non configuré. Utilisez la webcam live pour suivre le matériel.
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <template x-if="control.data?.warnings && control.data.warnings.length">
                            <div class="space-y-2 rounded-lg border border-yellow-500/40 bg-yellow-500/10 p-4 text-sm text-yellow-100">
                                <template x-for="(warning, idx) in control.data.warnings" :key="idx">
                                    <p x-text="warning"></p>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bookingControlPanel', (options = {}) => ({
        statusUrl: options.statusUrl,
        abortUrl: options.abortUrl,
        toggleUrl: options.toggleUrl,
        previewUrl: options.previewUrl,
        webcamUrl: options.webcamUrl || null,
        targetGuid: options.targetGuid || null,
        setGuid: options.setGuid || null,
        state: options.state || 'upcoming',
        csrfToken: null,
        loading: true,
        actionLoading: false,
        error: null,
        control: null,
        lastUpdatedLabel: '',
        targetEnabled: true,
        supportsToggle: Boolean(options.targetGuid),
        canAbort: options.state === 'active',
        previewImage: null,
        previewTimestamp: null,
        previewLoading: false,
        previewError: null,
        pollHandle: null,
        visibilityHandler: null,
        pollIntervalMs: 15000,

        init() {
            this.csrfToken = this.resolveCsrfToken();
            this.fetchStatus();
            this.startPolling();
            if (this.previewUrl) {
                this.fetchPreview();
            }
            this.visibilityHandler = this.handleVisibility.bind(this);
            document.addEventListener('visibilitychange', this.visibilityHandler);
            window.addEventListener('beforeunload', () => this.destroy());
        },

        destroy() {
            this.stopPolling();
            if (this.visibilityHandler) {
                document.removeEventListener('visibilitychange', this.visibilityHandler);
                this.visibilityHandler = null;
            }
        },

        startPolling() {
            if (this.pollHandle || this.pollIntervalMs <= 0) {
                return;
            }
            this.pollHandle = setInterval(() => this.fetchStatus(), this.pollIntervalMs);
        },

        stopPolling() {
            if (!this.pollHandle) {
                return;
            }
            clearInterval(this.pollHandle);
            this.pollHandle = null;
        },

        async fetchStatus() {
            if (!this.statusUrl) {
                return;
            }

            this.loading = true;
            try {
                const response = await fetch(this.statusUrl, {
                    headers: {
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message || 'Impossible de récupérer le statut du matériel.');
                }

                this.processStatus(payload);
                this.error = null;
            } catch (err) {
                this.error = err instanceof Error ? err.message : 'Erreur inattendue lors du chargement.';
            } finally {
                this.loading = false;
            }
        },

        processStatus(payload) {
            this.control = payload.control || null;
            this.state = payload.booking?.state || this.state;
            this.canAbort = this.state === 'active';

            if (this.control?.data?.target_guid) {
                this.targetGuid = this.control.data.target_guid;
            }

            if (this.control?.data?.set_guid) {
                this.setGuid = this.control.data.set_guid;
            }

            this.targetEnabled = this.control?.data?.target_enabled !== false;
            this.supportsToggle = Boolean(this.targetGuid);

            const timestamp = this.control?.timestamp || payload.control?.timestamp;
            this.lastUpdatedLabel = this.formatTimestamp(timestamp);

            if (this.previewUrl && this.state === 'active' && !this.previewLoading) {
                this.fetchPreview();
            }
        },

        async abortSession() {
            if (!this.canAbort || this.actionLoading || !this.abortUrl) {
                return;
            }

            if (!window.confirm("Confirmer l'arrêt immédiat de la session ?")) {
                return;
            }

            this.actionLoading = true;
            try {
                const response = await fetch(this.abortUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({}),
                });

                const payload = await response.json().catch(() => ({}));
                const message = payload.message || (response.ok ? 'Commande transmise à Voyager.' : "Erreur lors de l'envoi de la commande.");

                this.notify(response.ok ? 'success' : 'error', message);
            } catch (error) {
                const message = error instanceof Error ? error.message : "Erreur inattendue lors de l'envoi de la commande.";
                this.notify('error', message);
            } finally {
                this.actionLoading = false;
                this.fetchStatus();
            }
        },

        async toggleTarget() {
            if (!this.supportsToggle || this.actionLoading || !this.toggleUrl) {
                return;
            }

            if (!this.targetGuid) {
                this.notify('error', 'Identifiant Voyager indisponible pour cette cible.');
                return;
            }

            this.actionLoading = true;
            try {
                const response = await fetch(this.toggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        object_guid: this.targetGuid,
                        object_type: 1,
                        operation: this.targetEnabled ? 1 : 0,
                    }),
                });

                const payload = await response.json().catch(() => ({}));
                const message = payload.message || (response.ok ? 'Commande envoyée.' : 'Erreur lors de la commande.');

                if (response.ok) {
                    this.targetEnabled = !this.targetEnabled;
                }

                this.notify(response.ok ? 'success' : 'error', message);
            } catch (error) {
                const message = error instanceof Error ? error.message : "Erreur inattendue lors de l'envoi.";
                this.notify('error', message);
            } finally {
                this.actionLoading = false;
                this.fetchStatus();
            }
        },

        async fetchPreview() {
            if (!this.previewUrl || this.previewLoading) {
                return;
            }

            this.previewLoading = true;
            try {
                const response = await fetch(this.previewUrl, {
                    headers: {
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message || "Impossible de récupérer l'aperçu caméra.");
                }

                const preview = payload.preview || {};
                this.previewImage = preview.image || null;
                this.previewTimestamp = preview.timestamp || null;
                this.previewError = null;

                if (!this.previewImage && preview.meta?.message) {
                    this.previewError = preview.meta.message;
                }
            } catch (error) {
                const message = error instanceof Error ? error.message : "Erreur inattendue lors de la récupération de l'aperçu.";
                if (this.previewError !== message) {
                    this.notify('error', message);
                }
                this.previewError = message;
            } finally {
                this.previewLoading = false;
            }
        },

        handleVisibility() {
            if (document.hidden) {
                this.stopPolling();
            } else {
                this.fetchStatus();
                this.startPolling();
            }
        },

        resolveCsrfToken() {
            const meta = document.head.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        },

        notify(type, message) {
            if (!message) {
                return;
            }

            if (typeof window.showNotification === 'function') {
                window.showNotification('Contrôle du matériel', message, type === 'error' ? 'error' : type, 4500);
            } else {
                // eslint-disable-next-line no-alert
                alert(message);
            }
        },

        formatTimestamp(timestamp) {
            if (!timestamp) {
                return '';
            }

            const date = new Date(timestamp);
            if (Number.isNaN(date.getTime())) {
                return '';
            }

            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        },

        formatSeconds(value) {
            const total = Math.max(0, parseInt(value, 10) || 0);
            const hours = String(Math.floor(total / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((total % 3600) / 60)).padStart(2, '0');
            const seconds = String(total % 60).padStart(2, '0');

            return hours === '00' ? `${minutes}:${seconds}` : `${hours}:${minutes}:${seconds}`;
        },

        get sequenceProgress() {
            const raw = this.control?.data?.sequence?.progress ?? 0;
            const numeric = Number(raw);
            if (Number.isNaN(numeric)) {
                return 0;
            }
            return Math.max(0, Math.min(100, Math.round(numeric)));
        },

        get sequenceLabel() {
            const done = this.control?.data?.sequence?.shots_done ?? 0;
            const total = this.control?.data?.sequence?.shots_total ?? 0;
            return `Prises ${done}/${total}`;
        },

        previewTimestampLabel() {
            return this.formatTimestamp(this.previewTimestamp);
        },

        hasWebcamLink() {
            return typeof this.webcamUrl === 'string' && this.webcamUrl.length > 0;
        },
    }));
});
</script>
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
