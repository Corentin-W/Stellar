@extends('layouts.astral-app')

@section('title', 'Calendrier Admin - Réservations')

@push('styles')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
    <style>
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
        #admin-calendar {
            min-height: 600px;
        }
        #admin-calendar .fc-view {
            min-height: 560px;
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">📅 Calendrier des Réservations</h1>
                <p class="text-white/60">Vue d'ensemble de toutes les réservations</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.bookings.dashboard') }}" class="px-6 py-3 rounded-lg bg-white/5 text-white hover:bg-white/10 transition-colors inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
                <a href="{{ route('admin.bookings.blackouts') }}" class="px-6 py-3 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    Gérer les blocages
                </a>
            </div>
        </div>

        <!-- Filtre équipement -->
        <div class="dashboard-card p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-white font-medium mb-3">Filtrer par équipement</label>
                    <select id="equipment-filter" class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                        <option value="">Tous les équipements</option>
                        @foreach($equipments as $equipment)
                            <option value="{{ $equipment->id }}">{{ $equipment->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3">
                    <button onclick="calendar.today()" class="px-6 py-3 rounded-lg bg-blue-500/20 text-blue-400 hover:bg-blue-500/30 transition-colors">
                        Aujourd'hui
                    </button>
                    <button onclick="calendar.refetchEvents()" class="px-6 py-3 rounded-lg bg-purple-500/20 text-purple-400 hover:bg-purple-500/30 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Calendrier -->
        <div class="dashboard-card p-6">
            <div id="admin-calendar"></div>
        </div>

        <!-- Légende et statistiques -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <!-- Légende -->
            <div class="dashboard-card p-6">
                <h4 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    Légende des statuts
                </h4>
                <div class="grid grid-cols-1 gap-3">
                    <div class="flex items-center gap-3 p-3 bg-white/5 rounded-lg">
                        <div class="w-6 h-6 rounded" style="background-color: #f59e0b;"></div>
                        <div class="flex-1">
                            <div class="text-white font-medium">En attente</div>
                            <div class="text-white/60 text-sm">En attente de validation admin</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-white/5 rounded-lg">
                        <div class="w-6 h-6 rounded" style="background-color: #10b981;"></div>
                        <div class="flex-1">
                            <div class="text-white font-medium">Confirmée</div>
                            <div class="text-white/60 text-sm">Validée par un administrateur</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-white/5 rounded-lg">
                        <div class="w-6 h-6 rounded" style="background-color: #ef4444;"></div>
                        <div class="flex-1">
                            <div class="text-white font-medium">Rejetée</div>
                            <div class="text-white/60 text-sm">Refusée avec remboursement</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-white/5 rounded-lg">
                        <div class="w-6 h-6 rounded" style="background-color: #6b7280;"></div>
                        <div class="flex-1">
                            <div class="text-white font-medium">Annulée</div>
                            <div class="text-white/60 text-sm">Annulée par l'utilisateur</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-white/5 rounded-lg">
                        <div class="w-6 h-6 rounded" style="background-color: #3b82f6;"></div>
                        <div class="flex-1">
                            <div class="text-white font-medium">Terminée</div>
                            <div class="text-white/60 text-sm">Session complétée</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Raccourcis -->
            <div class="dashboard-card p-6">
                <h4 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Actions rapides
                </h4>
                <div class="space-y-3">
                    <a href="{{ route('admin.bookings.dashboard') }}" class="block p-4 bg-white/5 hover:bg-white/10 rounded-lg transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-white font-medium">Réservations en attente</div>
                                <div class="text-white/60 text-sm">Voir toutes les demandes</div>
                            </div>
                        </div>
                    </a>

                    @foreach($equipments->take(3) as $equipment)
                    <a href="{{ route('admin.bookings.time-slots', $equipment) }}" class="block p-4 bg-white/5 hover:bg-white/10 rounded-lg transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-white font-medium">{{ $equipment->name }}</div>
                                <div class="text-white/60 text-sm">Gérer les plages horaires</div>
                            </div>
                        </div>
                    </a>
                    @endforeach

                    <a href="{{ route('admin.bookings.blackouts') }}" class="block p-4 bg-red-500/10 hover:bg-red-500/20 rounded-lg transition-colors border border-red-500/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-red-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-white font-medium">Créer un blocage</div>
                                <div class="text-white/60 text-sm">Maintenance ou indisponibilité</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Aide -->
        <div class="dashboard-card p-6 mt-6 bg-blue-500/10 border-blue-500/30">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-blue-400 font-semibold mb-2">💡 Astuce</h4>
                    <p class="text-white/70 text-sm mb-2">
                        Cliquez sur une réservation pour voir ses détails et accéder aux actions de validation.
                    </p>
                    <ul class="text-white/60 text-sm space-y-1 list-disc list-inside">
                        <li>Utilisez le filtre pour voir un équipement spécifique</li>
                        <li>Changez de vue (Mois/Semaine/Jour) avec les boutons en haut</li>
                        <li>Les couleurs indiquent le statut de chaque réservation</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection



@push('scripts')
    @vite('resources/js/calendar.js')

    <script>
    let calendar;

    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('admin-calendar');
        const equipmentFilter = document.getElementById('equipment-filter');

        if (!calendarEl) {
            console.error('Calendar element not found');
            return;
        }

        // Initialiser le calendrier admin
        calendar = window.initAdminCalendar(calendarEl);

        // Rechargement lors du changement de filtre
        if (equipmentFilter) {
            equipmentFilter.addEventListener('change', function() {
                console.log('Filter changed to:', this.value || 'All');
                calendar.refetchEvents();
            });
        }

        console.log('✅ Admin calendar initialized');
    });
    </script>
@endpush
