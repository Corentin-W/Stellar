@extends('layouts.astral-app')

@php
    use Illuminate\Support\Str;
@endphp

@section('title', 'Calendrier de Réservation')

@push('styles')
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<style>
    /* Personnalisation du calendrier */
    .fc {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 20px;
    }
    .fc .fc-toolbar-title {
        color: white !important;
        font-size: 1.5rem;
    }
    .fc .fc-button {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: white !important;
    }
    .fc .fc-button:hover {
        background: rgba(255, 255, 255, 0.2) !important;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active {
        background: rgba(139, 92, 246, 0.5) !important;
    }
    .fc-theme-standard td,
    .fc-theme-standard th {
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    .fc-theme-standard .fc-scrollgrid {
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    .fc .fc-col-header-cell {
        background: rgba(255, 255, 255, 0.05) !important;
    }
    .fc .fc-col-header-cell-cushion {
        color: rgba(255, 255, 255, 0.7) !important;
    }
    .fc .fc-daygrid-day-number,
    .fc .fc-timegrid-slot-label-cushion {
        color: rgba(255, 255, 255, 0.6) !important;
    }
    .fc .fc-timegrid-slot {
        height: 3em !important;
    }
    #calendar {
        min-height: 640px;
    }
    .fc .fc-toolbar.fc-header-toolbar {
        margin-bottom: 1.5rem;
    }
    .animate-fade-in {
        animation: fadeIn .2s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .fc .pending-selection-highlight {
        background: rgba(139, 92, 246, 0.25) !important;
        border: 1px solid rgba(139, 92, 246, 0.45) !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen p-6">
    <div class="mx-auto max-w-7xl space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">📅 Calendrier de Réservation</h1>
                <p class="text-white/60">
                    Sélectionnez un équipement pour explorer ses créneaux disponibles et réserver en quelques clics.
                </p>
            </div>
            @auth
                <div class="flex flex-col gap-3 sm:items-end">
                    <a
                        href="{{ route('bookings.my-bookings', ['locale' => app()->getLocale()]) }}"
                        class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-3 text-sm font-semibold text-white transition-all hover:from-purple-700 hover:to-pink-700"
                    >
                        Voir mes réservations
                    </a>

                    <div class="dashboard-card flex items-center gap-3 px-5 py-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-500/20 text-purple-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-white/50">Vos crédits</p>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-semibold text-white">{{ number_format(auth()->user()->credits_balance) }}</span>
                                <span class="text-xs text-white/50">crédits</span>
                            </div>
                            <a href="{{ route('credits.shop', ['locale' => app()->getLocale()]) }}" class="text-xs font-medium text-purple-300 hover:text-purple-200">
                                Acheter des crédits
                            </a>
                        </div>
                    </div>
                </div>
            @endauth
        </div>

        <div class="dashboard-card p-6">
            <label for="equipment-select" class="block text-sm font-medium text-white/70">
                Équipement à réserver
            </label>
            <select
                id="equipment-select"
                class="mt-3 w-full rounded-lg border border-white/10 bg-white/5 px-4 py-3 text-white focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20"
            >
                <option value="">-- Sélectionnez un équipement --</option>
                @foreach($equipments as $equipment)
                    @php
                        $rawImages = $equipment->images;
                        if (is_string($rawImages)) {
                            $decodedImages = json_decode($rawImages, true);
                            $images = is_array($decodedImages) ? $decodedImages : [];
                        } elseif (is_array($rawImages)) {
                            $images = $rawImages;
                        } else {
                            $images = [];
                        }

                        $primaryImage = !empty($images) ? asset('storage/equipment/images/' . $images[0]) : '';
                        $description = Str::limit(strip_tags($equipment->description ?? ''), 240);
                    @endphp
                    <option
                        value="{{ $equipment->id }}"
                        data-name="{{ e($equipment->name) }}"
                        data-price="{{ $equipment->price_per_hour_credits }}"
                        data-location="{{ e($equipment->location ?? '') }}"
                        data-description="{{ e($description) }}"
                        data-image="{{ $primaryImage }}"
                        {{ $selectedEquipment && $selectedEquipment->id == $equipment->id ? 'selected' : '' }}
                    >
                        {{ $equipment->name }} ({{ $equipment->price_per_hour_credits }} crédits/heure)
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid gap-6 xl:grid-cols-[2fr_1fr]">
            <div class="space-y-6">
                <div class="dashboard-card overflow-hidden">
                    <div class="flex flex-col gap-4 border-b border-white/5 bg-white/5 px-6 py-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-white">Disponibilités en temps réel</h2>
                            <p class="text-sm text-white/60">Le calendrier s'actualise automatiquement selon l'équipement sélectionné.</p>
                        </div>
                        <button
                            id="calendar-refresh-button"
                            type="button"
                            class="inline-flex items-center gap-2 rounded-lg border border-white/10 px-3 py-2 text-sm font-medium text-white/70 transition hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Rafraîchir</span>
                        </button>
                    </div>
                    <div class="p-4 md:p-6">
                        <div id="calendar"></div>
                    </div>
                </div>

                <div class="dashboard-card space-y-4 p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-white/60">Légende du calendrier</h3>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="flex items-center gap-3 rounded-lg border border-white/10 bg-white/5 px-4 py-3">
                            <span class="h-3 w-3 rounded-full" style="background-color: #f59e0b;"></span>
                            <div>
                                <p class="text-sm font-medium text-white">En attente</p>
                                <p class="text-xs text-white/50">Réservation soumise</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg border border-white/10 bg-white/5 px-4 py-3">
                            <span class="h-3 w-3 rounded-full" style="background-color: #10b981;"></span>
                            <div>
                                <p class="text-sm font-medium text-white">Confirmée</p>
                                <p class="text-xs text-white/50">Validée par l'équipe</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg border border-white/10 bg-white/5 px-4 py-3">
                            <span class="h-3 w-3 rounded-full" style="background-color: #ef4444;"></span>
                            <div>
                                <p class="text-sm font-medium text-white">Indisponible</p>
                                <p class="text-xs text-white/50">Créneau bloqué</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="dashboard-card space-y-6 p-6" id="equipment-info-card">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Détails de l'équipement</h3>
                        <p class="text-sm text-white/60">Informations et plages horaires disponibles pour le matériel choisi.</p>
                    </div>
                    <div id="equipment-info-default" class="rounded-2xl border border-dashed border-white/10 bg-white/5 p-6 text-center text-white/60">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white/10">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="text-sm leading-relaxed">
                            Choisissez un équipement pour afficher sa description, sa localisation et ses disponibilités.
                        </p>
                    </div>
                    <div id="equipment-info-content" class="hidden space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="relative">
                                <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-xl border border-white/10 bg-white/5">
                                    <img id="equipment-image" class="hidden h-full w-full object-cover" alt="Visuel de l'équipement" />
                                    <span id="equipment-image-placeholder" class="text-xs uppercase tracking-wide text-white/40">Aucune image</span>
                                </div>
                            </div>
                            <div class="flex-1 space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <h4 id="equipment-info-name" class="text-xl font-semibold text-white"></h4>
                                    <span id="equipment-info-price" class="inline-flex items-center gap-1 rounded-full bg-yellow-500/10 px-3 py-1 text-xs font-semibold text-yellow-300"></span>
                                </div>
                                <p id="equipment-info-description" class="text-sm leading-relaxed text-white/70"></p>
                                <div class="flex items-center gap-2 text-sm text-white/60">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a7 7 0 00-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 00-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z" />
                                    </svg>
                                    <span id="equipment-info-location"></span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <h5 class="text-xs font-semibold uppercase tracking-wide text-white/50">Plages horaires actives</h5>
                            <ul id="availability-summary" class="space-y-2 text-sm text-white/70"></ul>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card space-y-3 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-white/60">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 19a7 7 0 110-14 7 7 0 010 14z" />
                        </svg>
                        Conseils de réservation
                    </h3>
                    <ul class="space-y-2 text-sm leading-relaxed text-white/70">
                        <li>Assurez-vous de disposer de crédits suffisants avant de confirmer votre créneau.</li>
                        <li>Les créneaux se réinitialisent lorsque vous changez d'équipement : vérifiez les disponibilités en temps réel.</li>
                        <li>Besoin d'un créneau particulier ? Contactez l'équipe via le support pour une demande personnalisée.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de réservation -->
<div id="booking-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <button type="button" class="modal-overlay fixed inset-0 bg-black/70 backdrop-blur-sm" aria-label="Fermer"></button>

        <div class="relative bg-gradient-to-br from-gray-900/90 to-gray-800/90 backdrop-blur-xl border border-white/10 rounded-2xl p-8 max-w-lg w-full shadow-2xl">
            <h3 class="text-2xl font-bold text-white mb-4">Confirmer la réservation</h3>

            <form id="booking-form" method="POST" action="{{ route('bookings.store', ['locale' => app()->getLocale()]) }}">
                @csrf
                <input type="hidden" name="equipment_id" id="modal-equipment-id">
                <input type="hidden" name="start_datetime" id="modal-start">
                <input type="hidden" name="end_datetime" id="modal-end">

                <div class="space-y-4 mb-6">
                    <div class="bg-white/5 rounded-lg p-4">
                        <div class="text-white/60 text-sm mb-1">Équipement</div>
                        <div class="text-white font-semibold" id="modal-equipment-name"></div>
                    </div>

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
                        <textarea
                            name="user_notes"
                            rows="3"
                            class="w-full rounded-lg border border-white/10 bg-white/5 px-4 py-3 text-white placeholder-white/40 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                            placeholder="Ajoutez des notes pour votre réservation..."
                        ></textarea>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" id="booking-modal-cancel" class="flex-1 rounded-lg bg-white/5 px-6 py-3 text-white transition-colors hover:bg-white/10">
                        Annuler
                    </button>
                    <button type="submit" class="flex-1 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-3 font-semibold text-white transition-all hover:from-purple-700 hover:to-pink-700">
                        Réserver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/fr.global.min.js'></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const equipmentSelect = document.getElementById('equipment-select');
        const infoDefault = document.getElementById('equipment-info-default');
        const infoContent = document.getElementById('equipment-info-content');
        const infoName = document.getElementById('equipment-info-name');
        const infoPrice = document.getElementById('equipment-info-price');
        const infoLocation = document.getElementById('equipment-info-location');
        const infoDescription = document.getElementById('equipment-info-description');
        const infoImage = document.getElementById('equipment-image');
        const infoImagePlaceholder = document.getElementById('equipment-image-placeholder');
        const summaryList = document.getElementById('availability-summary');
        const refreshButton = document.getElementById('calendar-refresh-button');
        const modalEl = document.getElementById('booking-modal');
        const modalCancelBtn = document.getElementById('booking-modal-cancel');
        const modalOverlay = modalEl ? modalEl.querySelector('.modal-overlay') : null;

        const eventsEndpoint = @json(route('bookings.events', ['locale' => app()->getLocale()]));
        const timeSlotsEndpoint = @json(route('bookings.time-slots', ['locale' => app()->getLocale()]));

        const dayLabels = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        const LOG_PREFIX = '[BookingCalendar]';

        function debugLog(message, payload = undefined) {
            if (payload !== undefined) {
                console.log(`${LOG_PREFIX} ${message}`, payload);
            } else {
                console.log(`${LOG_PREFIX} ${message}`);
            }
        }

        function notify(message, type = 'info') {
            if (typeof window.showNotification === 'function') {
                window.showNotification('Calendrier de réservation', message, type, 4500);
            } else {
                alert(message);
            }
        }

        let calendarInstance = null;
        let availableTimeSlots = [];
        let currentEquipmentId = equipmentSelect ? (equipmentSelect.value || '') : '';
        let pendingSelectionStart = null;
        let pendingSelectionHighlight = null;

        updateUrl(currentEquipmentId);
        updateEquipmentPanel(getSelectedOption(), []);
        debugLog('Page initialisée', { currentEquipmentId });

        if (equipmentSelect) {
            equipmentSelect.addEventListener('change', function() {
                currentEquipmentId = this.value || '';
                debugLog('Sélection équipement modifiée', { currentEquipmentId });
                updateEquipmentPanel(getSelectedOption(), []);
                resetPendingSelection();
                initOrRefreshCalendar(currentEquipmentId);
                updateUrl(currentEquipmentId);
                if (!currentEquipmentId) {
                    notify('Sélectionnez un équipement pour afficher ses disponibilités.', 'info');
                }
            });
        }

        if (refreshButton) {
            refreshButton.addEventListener('click', () => {
                if (!calendarInstance) {
                    debugLog('Rafraîchissement ignoré : pas d\'instance');
                    return;
                }

                refreshButton.classList.add('animate-pulse');
                calendarInstance.refetchEvents();
                debugLog('Rafraîchissement manuel des événements déclenché');

                if (currentEquipmentId) {
                    notify('Actualisation des disponibilités...', 'info');
                    loadTimeSlots(currentEquipmentId);
                } else {
                    setTimeout(() => refreshButton.classList.remove('animate-pulse'), 400);
                }
            });
        }

        if (modalCancelBtn) {
            modalCancelBtn.addEventListener('click', closeBookingModal);
        }

        if (modalOverlay) {
            modalOverlay.addEventListener('click', closeBookingModal);
        }

        window.addEventListener('booking-modal:closed', () => {
            resetPendingSelection();
        });

        ensureFullCalendar(() => initOrRefreshCalendar(currentEquipmentId));

        function showPendingSelectionHighlight(start, end = null) {
            if (!calendarInstance || !start) {
                return;
            }

            const highlightStart = new Date(start.getTime());
            const highlightEnd = end ? new Date(end.getTime()) : new Date(highlightStart.getTime() + 60 * 60 * 1000);

            removePendingSelectionHighlight();

            pendingSelectionHighlight = calendarInstance.addEvent({
                id: 'pending-selection-highlight',
                start: highlightStart,
                end: highlightEnd,
                display: 'background',
                overlap: false,
                classNames: ['pending-selection-highlight']
            });
        }

        function removePendingSelectionHighlight() {
            if (pendingSelectionHighlight) {
                pendingSelectionHighlight.remove();
                pendingSelectionHighlight = null;
            }
        }

        function initOrRefreshCalendar(equipmentId) {
            if (!calendarEl) {
                debugLog('Impossible d\'initialiser : élément calendrier introuvable');
                return;
            }

            if (!window.FullCalendar || typeof window.FullCalendar.Calendar !== 'function') {
                debugLog('FullCalendar indisponible, nouvelle tentative bientôt');
            ensureFullCalendar(() => initOrRefreshCalendar(equipmentId));
            return;
        }

        currentEquipmentId = equipmentId || '';
        availableTimeSlots = [];
        updateEquipmentPanel(getSelectedOption(), []);
        resetPendingSelection();
        debugLog('Initialisation/rafraîchissement du calendrier', { currentEquipmentId });

            if (calendarInstance) {
                removePendingSelectionHighlight();
                calendarInstance.destroy();
                debugLog('Ancienne instance détruite');
            }

            calendarInstance = new window.FullCalendar.Calendar(calendarEl, {
                locale: 'fr',
                initialView: 'timeGridWeek',
                expandRows: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                slotMinTime: '18:00:00',
                slotMaxTime: '30:00:00',
                scrollTime: '18:00:00',
                slotDuration: '01:00:00',
                snapDuration: '01:00:00',
                firstDay: 1,
                weekends: true,
                allDaySlot: false,
                height: 'auto',
                contentHeight: 640,
                selectable: true,
                selectMirror: true,
                selectOverlap: false,
                selectAllow(selectionInfo) {
                    debugLog('selectAllow', {
                        start: selectionInfo.start,
                        end: selectionInfo.end,
                        slotsLoaded: availableTimeSlots.length
                    });
                    if (!availableTimeSlots.length) {
                        return true;
                    }

                    const day = selectionInfo.start.getDay();
                    if (day !== selectionInfo.end.getDay()) {
                        debugLog('selectAllow refusé : sélection sur plusieurs jours');
                        return false;
                    }

                    return isSelectionWithinAvailability(selectionInfo.start, selectionInfo.end);
                },
                dateClick(info) {
                    const selectionStart = new Date(info.date.getTime());
                    debugLog('dateClick', { selectionStart });
                    handleClickSelection(selectionStart);
                },
                nowIndicator: true,
                editable: false,
                eventStartEditable: false,
                eventDurationEditable: false,
                eventSources: [
                    {
                        id: 'bookings',
                        url: eventsEndpoint,
                        method: 'GET',
                        extraParams() {
                            return { equipment_id: currentEquipmentId };
                        },
                        success(events) {
                            debugLog('Réservations chargées', { count: events.length, equipmentId: currentEquipmentId });
                        },
                        failure(error) {
                            console.error(LOG_PREFIX, 'Erreur lors du chargement des réservations', error);
                            notify('Impossible de charger les réservations pour cet équipement.', 'error');
                        }
                    },
                    {
                        id: 'time-slots',
                        events(info, successCallback) {
                            const slots = buildTimeSlotEvents(info.start, info.end);
                            debugLog('Construction des événements de disponibilité', { count: slots.length });
                            successCallback(slots);
                        }
                    }
                ],
                select(info) {
                    const now = new Date();
                    debugLog('select', { start: info.start, end: info.end });
                    if (info.start < now) {
                        notify('Vous ne pouvez pas réserver un créneau dans le passé.', 'warning');
                        calendarInstance.unselect();
                        return;
                    }

                    if (!isSelectionWithinAvailability(info.start, info.end)) {
                        notify('Veuillez sélectionner un créneau inclus dans les plages disponibles.', 'warning');
                        calendarInstance.unselect();
                        resetPendingSelection();
                        return;
                    }

                    if (typeof window.openBookingModal === 'function') {
                        debugLog('select -> ouverture du modal');
                        notify('Créneau sélectionné. Complétez le formulaire pour valider votre demande.', 'success');
                        showPendingSelectionHighlight(info.start, info.end);
                        window.openBookingModal(info.start, info.end);
                    } else {
                        console.error(LOG_PREFIX, 'openBookingModal indisponible.');
                        notify('Le formulaire de réservation est indisponible.', 'error');
                    }

                    calendarInstance.unselect();
                },
                eventClick(info) {
                    const props = info.event.extendedProps || {};
                    if (props.type !== 'booking') {
                        debugLog('Clic sur événement non réservable (ignoré)', props);
                        return;
                    }
                    let message = `Statut: ${props.status || 'N/A'}\n`;
                    if (props.cost) {
                        message += `Coût: ${props.cost} crédits\n`;
                    }
                    if (props.user_notes) {
                        message += `Notes: ${props.user_notes}`;
                    }
                    notify(message, 'info');
                },
                eventDidMount(info) {
                    if (info.event && info.el) {
                        info.el.setAttribute('title', info.event.title || 'Réservation');
                    }
                }
            });

            calendarInstance.render();
            loadTimeSlots(currentEquipmentId);
            notify('Calendrier prêt. Sélectionnez un créneau disponible pour réserver.', 'success');
        }

        function loadTimeSlots(equipmentId) {
            const option = getSelectedOption();

            if (!equipmentId) {
                availableTimeSlots = [];
                debugLog('Aucun équipement sélectionné, nettoyage des disponibilités');
                if (calendarInstance) {
                    calendarInstance.setOption('businessHours', []);
                    const slotsSource = calendarInstance.getEventSourceById('time-slots');
                    if (slotsSource) {
                        slotsSource.refetch();
                    }
                    calendarInstance.refetchEvents();
                }
                updateEquipmentPanel(option, availableTimeSlots);
                if (refreshButton) {
                    refreshButton.classList.remove('animate-pulse');
                }
                resetPendingSelection();
                return;
            }

            const requestedId = equipmentId;
            debugLog('Chargement des plages horaires', { equipmentId });

            fetch(`${timeSlotsEndpoint}?equipment_id=${encodeURIComponent(equipmentId)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Réponse réseau invalide');
                    }
                    return response.json();
                })
                .then(data => {
                    if (requestedId !== currentEquipmentId) {
                        debugLog('Réponse ignorée car équipement changé entre temps');
                        return;
                    }

                    availableTimeSlots = Array.isArray(data) ? data : [];
                    debugLog('Plages horaires reçues', { count: availableTimeSlots.length });

                    const businessHours = availableTimeSlots.map(slot => ({
                        daysOfWeek: [Number(slot.day_of_week)],
                        startTime: (slot.start_time || '').slice(0, 5),
                        endTime: (slot.end_time || '').slice(0, 5)
                    }));

                    if (calendarInstance) {
                        calendarInstance.setOption('businessHours', businessHours);
                        const slotSource = calendarInstance.getEventSourceById('time-slots');
                        if (slotSource) {
                            slotSource.refetch();
                        }
                        calendarInstance.refetchEvents();
                    }

                    updateEquipmentPanel(option, availableTimeSlots);
                    resetPendingSelection();
                })
                .catch(error => {
                    console.error(LOG_PREFIX, 'Erreur lors du chargement des plages horaires', error);
                    notify('Impossible de charger les plages horaires pour cet équipement.', 'error');
                })
                .finally(() => {
                    if (refreshButton) {
                        refreshButton.classList.remove('animate-pulse');
                    }
                });
        }

        function getSelectedOption() {
            if (!equipmentSelect) {
                return null;
            }
            const option = equipmentSelect.options[equipmentSelect.selectedIndex];
            return option && option.value ? option : null;
        }

        function updateEquipmentPanel(option, slots = []) {
            if (!infoDefault || !infoContent) {
                return;
            }

            if (!option) {
                infoDefault.classList.remove('hidden');
                infoContent.classList.add('hidden');
                debugLog('Panneau équipement réinitialisé (aucune sélection)');
                if (refreshButton) {
                    refreshButton.setAttribute('disabled', 'disabled');
                    refreshButton.classList.add('opacity-50', 'cursor-not-allowed');
                }
                if (summaryList) {
                    summaryList.innerHTML = '';
                }
                return;
            }

            infoDefault.classList.add('hidden');
            infoContent.classList.remove('hidden');
            debugLog('Panneau équipement mis à jour', { name: option.dataset.name, slots: slots.length });

            if (refreshButton) {
                refreshButton.removeAttribute('disabled');
                refreshButton.classList.remove('opacity-50', 'cursor-not-allowed');
            }

            if (infoName) {
                infoName.textContent = option.dataset.name || option.textContent.trim();
            }

            if (infoPrice) {
                const price = option.dataset.price;
                infoPrice.textContent = price ? `${price} crédits / heure` : 'Tarif non renseigné';
            }

            if (infoLocation) {
                infoLocation.textContent = option.dataset.location || 'Localisation non renseignée';
            }

            if (infoDescription) {
                infoDescription.textContent = option.dataset.description || 'Aucune description disponible pour cet équipement.';
            }

            if (infoImage && infoImagePlaceholder) {
                if (option.dataset.image) {
                    infoImage.src = option.dataset.image;
                    infoImage.alt = option.dataset.name ? `Visuel de ${option.dataset.name}` : 'Visuel de l\'équipement';
                    infoImage.classList.remove('hidden');
                    infoImagePlaceholder.classList.add('hidden');
                } else {
                    infoImage.src = '';
                    infoImage.classList.add('hidden');
                    infoImagePlaceholder.classList.remove('hidden');
                }
            }

            updateAvailabilitySummary(slots);
        }

        function updateAvailabilitySummary(slots = []) {
            if (!summaryList) {
                return;
            }

            summaryList.innerHTML = '';

            if (!slots.length) {
                const li = document.createElement('li');
                li.className = 'rounded-lg border border-white/10 bg-white/5 px-4 py-3 text-sm text-white/70';
                li.textContent = 'Aucune plage horaire active pour le moment.';
                summaryList.appendChild(li);
                return;
            }

            const grouped = {};
            slots.forEach(slot => {
                const dayIndex = Number(slot.day_of_week);
                if (!grouped[dayIndex]) {
                    grouped[dayIndex] = [];
                }
                grouped[dayIndex].push(slot);
            });

            for (let dayIndex = 0; dayIndex < dayLabels.length; dayIndex += 1) {
                const daySlots = grouped[dayIndex];
                if (!daySlots || !daySlots.length) {
                    continue;
                }

                daySlots.sort((a, b) => (a.start_time || '').localeCompare(b.start_time || ''));

                const li = document.createElement('li');
                li.className = 'flex items-start justify-between gap-3 rounded-lg border border-white/10 bg-white/5 px-4 py-3';

                const dayName = document.createElement('span');
                dayName.className = 'font-semibold text-white';
                dayName.textContent = dayLabels[dayIndex] || 'Jour';

                const ranges = document.createElement('span');
                ranges.className = 'text-right text-sm text-white/70';
                ranges.textContent = daySlots
                    .map(slot => formatSlotRange(slot.start_time, slot.end_time))
                    .join(' · ');

                li.appendChild(dayName);
                li.appendChild(ranges);
                summaryList.appendChild(li);
            }
        }

        function formatSlotRange(start, end) {
            const startLabel = formatTimeLabel(start);
            const endLabel = formatTimeLabel(end);
            if (!start || !end) {
                return `${startLabel} → ${endLabel}`;
            }
            if (end <= start) {
                return `${startLabel} → ${endLabel} (+1j)`;
            }
            return `${startLabel} → ${endLabel}`;
        }

        function formatTimeLabel(timeString) {
            if (!timeString) {
                return '--';
            }
            const [hours = '00', minutes = '00'] = timeString.split(':');
            return `${hours}h${minutes}`;
        }

        function ensureFullCalendar(callback) {
            if (window.FullCalendar && typeof window.FullCalendar.Calendar === 'function') {
                debugLog('FullCalendar disponible immédiatement');
                callback();
                return;
            }
            let attempts = 0;
            const maxAttempts = 30;
            const interval = setInterval(() => {
                if (window.FullCalendar && typeof window.FullCalendar.Calendar === 'function') {
                    clearInterval(interval);
                    debugLog('FullCalendar chargé après attente');
                    callback();
                } else if (attempts >= maxAttempts) {
                    clearInterval(interval);
                    console.error(LOG_PREFIX, 'FullCalendar ne s\'est pas chargé correctement.');
                    notify('Le module calendrier ne s\'est pas chargé correctement. Rechargez la page.', 'error');
                }
                attempts += 1;
            }, 100);
        }

        function createDateWithTime(baseDate, timeString) {
            const [hours = '0', minutes = '0', seconds = '0'] = (timeString || '00:00:00').split(':');
            const date = new Date(baseDate);
            date.setHours(parseInt(hours, 10), parseInt(minutes, 10), parseInt(seconds, 10), 0);
            return date;
        }

        function isWithinSlot(slot, startDate, endDate) {
            const slotStart = createDateWithTime(startDate, slot.start_time);
            const slotEnd = createDateWithTime(startDate, slot.end_time);

            if (slotEnd <= slotStart) {
                slotEnd.setDate(slotEnd.getDate() + 1);
            }

            return startDate >= slotStart && endDate <= slotEnd;
        }

        function isSelectionWithinAvailability(selectionStart, selectionEnd) {
            if (!availableTimeSlots.length) {
                debugLog('Aucune plage active : la sélection est autorisée par défaut');
                return true;
            }

            const day = selectionStart.getDay();
            if (day !== selectionEnd.getDay()) {
                debugLog('Sélection refusée : chevauchement sur plusieurs jours');
                return false;
            }

            const matchingSlots = availableTimeSlots.filter(slot => Number(slot.day_of_week) === day);
            const allowed = matchingSlots.some(slot => isWithinSlot(slot, selectionStart, selectionEnd));
            debugLog('Vérification de disponibilité', {
                selectionStart,
                selectionEnd,
                matchingSlotsCount: matchingSlots.length,
                allowed
            });
            return allowed;
        }

        function handleClickSelection(date) {
            const normalizedDate = new Date(date.getTime());
            normalizedDate.setSeconds(0, 0);

            const now = new Date();
            if (normalizedDate < now) {
                notify('Vous ne pouvez pas réserver un créneau dans le passé.', 'warning');
                resetPendingSelection();
                return;
            }

            if (!pendingSelectionStart) {
                pendingSelectionStart = normalizedDate;
                showPendingSelectionHighlight(pendingSelectionStart);
                notify('Heure de début enregistrée. Cliquez sur l\'heure de fin souhaitée.', 'info');
                debugLog('Début de sélection défini', { start: pendingSelectionStart });
                return;
            }

            if (normalizedDate.getTime() === pendingSelectionStart.getTime()) {
                const defaultEnd = new Date(pendingSelectionStart.getTime() + 60 * 60 * 1000);
                if (!isSelectionWithinAvailability(pendingSelectionStart, defaultEnd)) {
                    notify('Cette plage n\'est pas entièrement disponible.', 'warning');
                    debugLog('Plage par défaut invalide', { start: pendingSelectionStart, end: defaultEnd });
                    resetPendingSelection();
                    return;
                }
                showPendingSelectionHighlight(pendingSelectionStart, defaultEnd);
                notify('Créneau sélectionné. Complétez le formulaire pour valider votre demande.', 'success');
                debugLog('Plage validée (1 heure) via double clic', { start: pendingSelectionStart, end: defaultEnd });
                const start = pendingSelectionStart;
                pendingSelectionStart = null;
                window.openBookingModal(start, defaultEnd);
                return;
            }

            if (normalizedDate.getDay() !== pendingSelectionStart.getDay()) {
                pendingSelectionStart = normalizedDate;
                showPendingSelectionHighlight(pendingSelectionStart);
                notify('Nouvelle date sélectionnée. Choisissez l\'heure de fin sur cette journée.', 'info');
                debugLog('Début de sélection repositionné sur un autre jour', { start: pendingSelectionStart });
                return;
            }

            if (normalizedDate <= pendingSelectionStart) {
                pendingSelectionStart = normalizedDate;
                showPendingSelectionHighlight(pendingSelectionStart);
                notify('Heure de début mise à jour. Cliquez sur une heure plus tard pour définir la fin.', 'info');
                debugLog('Début de sélection mis à jour (cliqué plus tôt)', { start: pendingSelectionStart });
                return;
            }

            const selectionStart = pendingSelectionStart;
            let selectionEnd = new Date(normalizedDate.getTime() + 60 * 60 * 1000);

            if (!isSelectionWithinAvailability(selectionStart, selectionEnd)) {
                notify('Cette plage n\'est pas entièrement disponible.', 'warning');
                debugLog('Plage sélectionnée invalide', { selectionStart, selectionEnd });
                resetPendingSelection();
                return;
            }

            showPendingSelectionHighlight(selectionStart, selectionEnd);
            notify('Créneau sélectionné. Complétez le formulaire pour valider votre demande.', 'success');
            debugLog('Plage validée via clics successifs', { selectionStart, selectionEnd });
            pendingSelectionStart = null;
            window.openBookingModal(selectionStart, selectionEnd);
        }

        function buildTimeSlotEvents(rangeStart, rangeEnd) {
            if (!availableTimeSlots.length) {
                return [];
            }

            const events = [];
            const current = new Date(rangeStart.getTime());
            current.setHours(0, 0, 0, 0);
            const end = new Date(rangeEnd.getTime());

            while (current < end) {
                const daySlots = availableTimeSlots.filter(slot => Number(slot.day_of_week) === current.getDay());

                daySlots.forEach(slot => {
                    const slotStart = createDateWithTime(current, slot.start_time);
                    const slotEnd = createDateWithTime(current, slot.end_time);

                    if (slotEnd <= slotStart) {
                        slotEnd.setDate(slotEnd.getDate() + 1);
                    }

                    events.push({
                        id: `slot-${slot.id}-${slotStart.toISOString()}`,
                        start: slotStart.toISOString(),
                        end: slotEnd.toISOString(),
                        display: 'background',
                        backgroundColor: 'rgba(59, 130, 246, 0.12)',
                        borderColor: 'rgba(59, 130, 246, 0.32)',
                        classNames: ['available-time-slot']
                    });
                });

                current.setDate(current.getDate() + 1);
            }

            return events;
        }

        function updateUrl(equipmentId) {
            if (!window.history.replaceState) {
                return;
            }
            const url = new URL(window.location.href);
            if (equipmentId) {
                url.searchParams.set('equipment_id', equipmentId);
            } else {
                url.searchParams.delete('equipment_id');
            }
            window.history.replaceState({}, '', url);
        }

        function resetPendingSelection() {
            if (pendingSelectionStart) {
                debugLog('Réinitialisation de la sélection en cours');
            }
            pendingSelectionStart = null;
            removePendingSelectionHighlight();
        }
    });

    window.openBookingModal = function(start, end) {
        const equipmentSelect = document.getElementById('equipment-select');
        if (!equipmentSelect) {
            return;
        }

        const selectedOption = equipmentSelect.options[equipmentSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            alert('Veuillez sélectionner un équipement');
            return;
        }

        console.log('[BookingCalendar] Ouverture du modal de réservation', {
            start,
            end,
            equipmentId: selectedOption.value
        });

        const equipmentId = selectedOption.value;
        const pricePerHour = Number(selectedOption.dataset.price || 0);
        const equipmentName = selectedOption.dataset.name || selectedOption.textContent.trim();

        const hoursDiff = (end - start) / (1000 * 60 * 60);
        const formattedHours = Number.isInteger(hoursDiff)
            ? `${hoursDiff} heure(s)`
            : `${hoursDiff.toFixed(2)} heure(s)`;

        const estimatedCost = Number.isFinite(pricePerHour)
            ? Math.max(0, Math.round(hoursDiff * pricePerHour))
            : 0;

        const costField = document.getElementById('modal-cost');
        if (costField) {
            if (pricePerHour > 0) {
                const formattedCost = new Intl.NumberFormat('fr-FR').format(estimatedCost);
                costField.textContent = `${formattedCost} crédits`;
            } else {
                costField.textContent = 'Tarif non disponible';
            }
        }

        const equipmentNameField = document.getElementById('modal-equipment-name');
        if (equipmentNameField) {
            equipmentNameField.textContent = equipmentName;
        }

        const durationField = document.getElementById('modal-duration');
        if (durationField) {
            durationField.textContent = formattedHours;
        }

        const periodField = document.getElementById('modal-period');
        if (periodField) {
            periodField.textContent = `${start.toLocaleString('fr-FR')} → ${end.toLocaleString('fr-FR')}`;
        }

        const equipmentInput = document.getElementById('modal-equipment-id');
        if (equipmentInput) {
            equipmentInput.value = equipmentId;
        }

        const startInput = document.getElementById('modal-start');
        if (startInput) {
            startInput.value = start.toISOString();
        }

        const endInput = document.getElementById('modal-end');
        if (endInput) {
            endInput.value = end.toISOString();
        }

        const modalElement = document.getElementById('booking-modal');
        if (modalElement) {
            modalElement.classList.remove('hidden');
            modalElement.classList.add('animate-fade-in');
            modalElement.setAttribute('aria-hidden', 'false');
        } else {
            console.warn('[BookingCalendar] Élément de modal introuvable lors de l\'ouverture.');
        }
    };

    function closeBookingModal() {
        const modalEl = document.getElementById('booking-modal');
        if (modalEl) {
            modalEl.classList.add('hidden');
            modalEl.classList.remove('animate-fade-in');
            modalEl.setAttribute('aria-hidden', 'true');
        }
        resetPendingSelection();
        window.dispatchEvent(new CustomEvent('booking-modal:closed'));
    }
    </script>
@endpush
