import * as FullCalendar from 'fullcalendar';
import frLocale from '@fullcalendar/core/locales/fr';

// Environnement global FullCalendar (bundle Vite) avec locale fr
if (!FullCalendar.globalLocales.some(locale => locale.code === frLocale.code)) {
    FullCalendar.globalLocales.push(frLocale);
}

if (typeof window !== 'undefined') {
    window.FullCalendar = FullCalendar;
}

// FullCalendar - Gestion des calendriers de réservation
// Ce fichier contient les fonctions pour initialiser les calendriers admin et utilisateur

/**
 * Initialise le calendrier de réservation utilisateur
 * @param {HTMLElement} calendarEl - L'élément DOM où le calendrier sera rendu
 * @param {number} equipmentId - L'ID de l'équipement sélectionné
 * @returns {Calendar} Instance du calendrier FullCalendar
 */
window.initBookingCalendar = function(calendarEl, equipmentId) {
    if (!calendarEl) {
        console.error('Calendar element not found');
        return null;
    }

    let availableTimeSlots = [];

    const calendar = new FullCalendar.Calendar(calendarEl, {
        // Configuration de base
        locale: 'fr',
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        // Horaires
        slotMinTime: '18:00:00',
        slotMaxTime: '30:00:00',
        scrollTime: '18:00:00',
        slotDuration: '01:00:00',
        snapDuration: '01:00:00',

        // Jours de la semaine
        firstDay: 1, // Lundi
        weekends: true,
        allDaySlot: false,

        // Hauteur
        height: 'auto',
        contentHeight: 600,

        // Sélection
        selectable: true,
        selectMirror: true,
        selectOverlap: false,
        selectAllow: function(selectionInfo) {
            if (!availableTimeSlots.length) {
                return true;
            }

            const day = selectionInfo.start.getDay();

            if (day !== selectionInfo.end.getDay()) {
                return false;
            }

            return availableTimeSlots
                .filter(slot => slot.day_of_week === day)
                .some(slot => {
                    const slotStart = createDateWithTime(selectionInfo.start, slot.start_time);
                    const slotEnd = createDateWithTime(selectionInfo.start, slot.end_time);

                    if (slotEnd <= slotStart) {
                        slotEnd.setDate(slotEnd.getDate() + 1);
                    }

                    return selectionInfo.start >= slotStart && selectionInfo.end <= slotEnd;
                });
        },

        // Plages horaires visibles
        businessHours: [],

        // Événements
        eventSources: [
            {
                id: 'bookings',
                url: '/bookings/events',
                method: 'GET',
                extraParams: {
                    equipment_id: equipmentId
                },
                failure: function(error) {
                    console.error('Error loading events:', error);
                    alert('Erreur lors du chargement des événements');
                }
            },
            {
                id: 'time-slots',
                events: function(info, successCallback) {
                    successCallback(buildTimeSlotEvents(info.start, info.end));
                }
            }
        ],

        // Callback de sélection
        select: function(info) {
            console.log('Time slot selected:', info);

            // Vérifier si la sélection est dans le futur
            const now = new Date();
            if (info.start < now) {
                alert('Vous ne pouvez pas réserver dans le passé');
                calendar.unselect();
                return;
            }

            // Ouvrir le modal de réservation
            if (typeof window.openBookingModal === 'function') {
                window.openBookingModal(info.start, info.end);
            } else {
                console.error('openBookingModal function not found');
            }

            calendar.unselect();
        },

        // Callback de clic sur événement
        eventClick: function(info) {
            console.log('Event clicked:', info.event);

            // Afficher les détails de la réservation
            const props = info.event.extendedProps;
            let message = `Statut: ${props.status}\n`;

            if (props.cost) {
                message += `Coût: ${props.cost} crédits\n`;
            }

            if (props.user_notes) {
                message += `Notes: ${props.user_notes}`;
            }

            alert(message);
        },

        // Style des événements
        eventDidMount: function(info) {
            info.el.setAttribute('title', info.event.title);
        },

        // Boutons personnalisés
        customButtons: {
            refresh: {
                text: '⟳',
                click: function() {
                    calendar.refetchEvents();
                }
            }
        },

        // Options d'affichage
        nowIndicator: true,
        editable: false,
        eventStartEditable: false,
        eventDurationEditable: false
    });

    // Rendre le calendrier
    calendar.render();

    loadTimeSlots();

    console.log('✅ Booking calendar initialized for equipment:', equipmentId);
    return calendar;

    async function loadTimeSlots() {
        try {
            const response = await fetch(`/bookings/time-slots?equipment_id=${equipmentId}`);

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            availableTimeSlots = await response.json();

            const businessHours = availableTimeSlots.map(slot => ({
                daysOfWeek: [slot.day_of_week],
                startTime: (slot.start_time || '').slice(0, 5),
                endTime: (slot.end_time || '').slice(0, 5)
            }));

            calendar.setOption('businessHours', businessHours);

            const slotSource = calendar.getEventSourceById('time-slots');
            if (slotSource) {
                slotSource.refetch();
            }
        } catch (error) {
            console.error('Error loading time slots:', error);
        }
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
            const daySlots = availableTimeSlots.filter(slot => slot.day_of_week === current.getDay());

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
                    backgroundColor: 'rgba(59, 130, 246, 0.15)',
                    borderColor: 'rgba(59, 130, 246, 0.3)',
                    classNames: ['available-time-slot'],
                    extendedProps: {
                        slotId: slot.id,
                        maxBookings: slot.max_concurrent_bookings
                    }
                });
            });

            current.setDate(current.getDate() + 1);
        }

        return events;
    }

    function createDateWithTime(baseDate, timeString) {
        const [hours = '0', minutes = '0', seconds = '0'] = (timeString || '00:00:00').split(':');
        const date = new Date(baseDate);
        date.setHours(parseInt(hours, 10), parseInt(minutes, 10), parseInt(seconds, 10), 0);
        return date;
    }
};

/**
 * Initialise le calendrier administrateur (vue globale)
 * @param {HTMLElement} calendarEl - L'élément DOM où le calendrier sera rendu
 * @returns {Calendar} Instance du calendrier FullCalendar
 */
window.initAdminCalendar = function(calendarEl) {
    if (!calendarEl) {
        console.error('Calendar element not found');
        return null;
    }

    const calendar = new FullCalendar.Calendar(calendarEl, {
        // Configuration de base
        locale: 'fr',
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },

        // Horaires
        slotMinTime: '18:00:00',
        slotMaxTime: '30:00:00',
        scrollTime: '18:00:00',
        slotDuration: '01:00:00',

        // Jours de la semaine
        firstDay: 1, // Lundi
        weekends: true,
        allDaySlot: false,

        // Hauteur
        height: 'auto',
        contentHeight: 700,

        // Pas de sélection pour l'admin (juste visualisation)
        selectable: false,

        // Événements
        events: function(info, successCallback, failureCallback) {
            // Récupérer le filtre d'équipement
            const equipmentFilter = document.getElementById('equipment-filter');
            const equipmentId = equipmentFilter ? equipmentFilter.value : '';

            // Construire l'URL avec les paramètres
            const params = new URLSearchParams({
                start: info.startStr,
                end: info.endStr
            });

            if (equipmentId) {
                params.append('equipment_id', equipmentId);
            }

            // Requête AJAX
            fetch(`/admin/bookings/calendar/events?${params.toString()}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(events => {
                    console.log(`Loaded ${events.length} events`);
                    successCallback(events);
                })
                .catch(error => {
                    console.error('Error loading admin events:', error);
                    failureCallback(error);
                });
        },

        // Callback de clic sur événement
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;

            // Construire le message d'info
            let message = `📅 ${event.title}\n\n`;
            message += `⏰ ${event.start.toLocaleString('fr-FR')} → ${event.end.toLocaleString('fr-FR')}\n`;

            if (props.equipment) {
                message += `🔭 Équipement: ${props.equipment}\n`;
            }

            if (props.user) {
                message += `👤 Utilisateur: ${props.user}\n`;
            }

            message += `📊 Statut: ${props.status || 'N/A'}\n`;

            if (props.cost) {
                message += `💰 Coût: ${props.cost} crédits\n`;
            }

            if (props.notes) {
                message += `📝 Notes: ${props.notes}\n`;
            }

            // Afficher et proposer d'aller aux détails
            if (confirm(message + '\n\nVoulez-vous voir les détails complets ?')) {
                // Rediriger vers la page de détails de la réservation
                if (props.booking_id) {
                    window.location.href = `/admin/bookings/dashboard#booking-${props.booking_id}`;
                }
            }
        },

        // Style des événements
        eventDidMount: function(info) {
            // Ajouter un tooltip
            const props = info.event.extendedProps;
            let tooltip = `${info.event.title}\n`;
            tooltip += `Statut: ${props.status || 'N/A'}`;

            info.el.setAttribute('title', tooltip);

            // Ajouter une classe CSS basée sur le statut
            if (props.status) {
                info.el.classList.add(`booking-status-${props.status}`);
            }
        },

        // Options d'affichage
        nowIndicator: true,
        editable: false,
        eventStartEditable: false,
        eventDurationEditable: false,

        // Vue liste
        views: {
            listWeek: {
                buttonText: 'Liste'
            }
        },

        // Messages de chargement
        loading: function(isLoading) {
            if (isLoading) {
                console.log('Loading events...');
            } else {
                console.log('Events loaded');
            }
        }
    });

    // Rendre le calendrier
    calendar.render();

    console.log('✅ Admin calendar initialized');
    return calendar;
};

// Les fonctions sont déjà disponibles globalement via window.initBookingCalendar et window.initAdminCalendar
console.log('📅 Calendar module loaded successfully');

// Inform the UI when the calendar helpers are ready so views can safely initialize them
if (typeof window !== 'undefined') {
    window.bookingCalendarReady = true;
}
