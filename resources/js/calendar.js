// resources/js/calendar.js
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import frLocale from '@fullcalendar/core/locales/fr';

// Exporter pour utilisation globale
window.FullCalendar = Calendar;
window.FullCalendarPlugins = {
    dayGridPlugin,
    timeGridPlugin,
    interactionPlugin,
    listPlugin,
    frLocale
};

// Fonction d'initialisation du calendrier utilisateur
window.initBookingCalendar = function(calendarEl, equipmentId) {
    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
        locale: frLocale,
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        slotMinTime: '18:00:00',
        slotMaxTime: '06:00:00',
        allDaySlot: false,
        selectable: true,
        selectMirror: true,
        nowIndicator: true,
        height: 'auto',

        events: {
            url: '/bookings/events',
            method: 'GET',
            extraParams: {
                equipment_id: equipmentId
            },
            failure: function(error) {
                console.error('Error loading events:', error);
            }
        },

        select: function(info) {
            if (window.openBookingModal) {
                window.openBookingModal(info.start, info.end);
            }
        },

        eventClick: function(info) {
            const props = info.event.extendedProps;
            if (props.type === 'blackout') {
                alert('🚫 ' + props.reason + '\n\n' + (props.description || ''));
            } else if (props.isOwn) {
                if (confirm('Voulez-vous voir les détails de votre réservation ?')) {
                    window.location.href = '/bookings/my-bookings';
                }
            }
        }
    });

    calendar.render();
    return calendar;
};

// Fonction d'initialisation du calendrier admin
window.initAdminCalendar = function(calendarEl) {
    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
        locale: frLocale,
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'Aujourd\'hui',
            month: 'Mois',
            week: 'Semaine',
            day: 'Jour',
            list: 'Liste'
        },
        slotMinTime: '06:00:00',
        slotMaxTime: '24:00:00',
        allDaySlot: false,
        nowIndicator: true,
        editable: false,
        selectable: false,
        height: 'auto',

        events: function(info, successCallback, failureCallback) {
            const equipmentFilter = document.getElementById('equipment-filter');
            const params = new URLSearchParams({
                start: info.startStr,
                end: info.endStr,
                equipment_id: equipmentFilter ? equipmentFilter.value : ''
            });

            fetch('/admin/bookings/calendar/events?' + params)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => {
                    console.error('Error loading events:', error);
                    failureCallback(error);
                });
        },

        eventClick: function(info) {
            if (info.event.url) {
                if (info.jsEvent.ctrlKey || info.jsEvent.metaKey) {
                    window.open(info.event.url, '_blank');
                } else {
                    window.location.href = info.event.url;
                }
                info.jsEvent.preventDefault();
            }
        },

        eventDidMount: function(info) {
            const props = info.event.extendedProps;
            const tooltipText = `${props.userName} - ${props.equipmentName}\n` +
                              `Statut: ${getStatusLabel(props.status)}\n` +
                              `Coût: ${props.cost} crédits`;
            info.el.title = tooltipText;
        }
    });

    calendar.render();
    return calendar;
};

// Helper function
function getStatusLabel(status) {
    const labels = {
        'pending': 'En attente',
        'confirmed': 'Confirmée',
        'rejected': 'Rejetée',
        'cancelled': 'Annulée',
        'completed': 'Terminée'
    };
    return labels[status] || status;
}

console.log('✅ FullCalendar module loaded');
