{{-- resources/views/admin/support/dashboard.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Dashboard Support Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Dashboard Support</h1>
            <p class="text-gray-300">Gestion et supervision du système de support</p>
        </div>
        <div class="flex items-center space-x-4 mt-4 sm:mt-0">
            <a href="{{ route('admin.support.tickets.index') }}"
               class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-300">
                Tous les tickets
            </a>
            <a href="{{ route('admin.support.categories.index') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-300">
                Catégories
            </a>
            <a href="{{ route('admin.support.templates.index') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-300">
                Templates
            </a>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        {{-- Total des tickets --}}
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm font-medium">Total Tickets</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($stats['total_tickets']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <span class="text-blue-400 text-2xl">🎫</span>
                </div>
            </div>
        </div>

        {{-- Tickets ouverts --}}
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm font-medium">Tickets Ouverts</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($stats['open_tickets']) }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
                    <span class="text-orange-400 text-2xl">📂</span>
                </div>
            </div>
        </div>

        {{-- Tickets urgents --}}
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm font-medium">Tickets Urgents</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($stats['urgent_tickets']) }}</p>
                </div>
                <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center">
                    <span class="text-red-400 text-2xl">🚨</span>
                </div>
            </div>
        </div>

        {{-- Nouveaux aujourd'hui --}}
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm font-medium">Aujourd'hui</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($stats['today_tickets']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <span class="text-green-400 text-2xl">📅</span>
                </div>
            </div>
        </div>

        {{-- Temps de réponse moyen --}}
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm font-medium">Temps Réponse Moyen</p>
                    <p class="text-3xl font-bold text-white">{{ $stats['avg_response_time'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <span class="text-purple-400 text-2xl">⏱️</span>
                </div>
            </div>
        </div>

        {{-- Résolus aujourd'hui --}}
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm font-medium">Résolus Aujourd'hui</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($stats['resolved_today']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <span class="text-green-400 text-2xl">✅</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Tickets urgents --}}
    @if($urgentTickets->count() > 0)
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-white mb-4">🚨 Tickets Urgents</h2>
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-red-500/20">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Ticket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Utilisateur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Catégorie</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Créé</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($urgentTickets as $ticket)
                        <tr class="hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-white font-medium text-sm">{{ $ticket->subject }}</div>
                                    <div class="text-gray-400 text-xs">{{ $ticket->ticket_number }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-white text-sm">{{ $ticket->user->name }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                      style="background-color: {{ $ticket->category->color }}20; color: {{ $ticket->category->color }};">
                                    {{ $ticket->category->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-300 text-sm">{{ $ticket->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.support.tickets.show', $ticket) }}"
                                   class="text-purple-400 hover:text-purple-300 text-sm font-medium">
                                    Traiter
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Mes tickets assignés --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div>
            <h2 class="text-xl font-semibold text-white mb-4">Mes Tickets Assignés</h2>
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700">
                <div class="divide-y divide-gray-600 max-h-64 overflow-y-auto">
                    @forelse($myTickets as $ticket)
                        <div class="px-6 py-3 hover:bg-gray-700/30 transition-colors">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-white font-medium text-sm truncate">{{ $ticket->subject }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $ticket->status === 'open' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $ticket->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $ticket->status === 'waiting_user' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $ticket->status === 'waiting_admin' ? 'bg-red-100 text-red-800' : '' }}
                                    ml-2">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-400 flex items-center justify-between">
                                <span>{{ $ticket->user->name }}</span>
                                <a href="{{ route('admin.support.tickets.show', $ticket->id) }}"
                                   class="text-purple-400 hover:text-purple-300">
                                    Traiter
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-4 text-center text-gray-400 text-sm">
                            Aucun ticket assigné
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Tickets récents --}}
        <div>
            <h2 class="text-xl font-semibold text-white mb-4">Tickets Récents</h2>
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700">
                <div class="divide-y divide-gray-600 max-h-64 overflow-y-auto">
                    @forelse($recentTickets as $ticket)
                        <div class="px-6 py-3 hover:bg-gray-700/30 transition-colors">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-white font-medium text-sm truncate">{{ $ticket->subject }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $ticket->priority === 'urgent' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $ticket->priority === 'high' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $ticket->priority === 'normal' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $ticket->priority === 'low' ? 'bg-gray-100 text-gray-800' : '' }}
                                    ml-2">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-400 flex items-center justify-between">
                                <div class="flex items-center">
                                    <span>{{ $ticket->user->name }}</span>
                                    <span class="mx-1">•</span>
                                    <span>{{ $ticket->created_at->diffForHumans() }}</span>
                                </div>
                                <a href="{{ route('admin.support.tickets.show', $ticket->id) }}"
                                   class="text-purple-400 hover:text-purple-300">
                                    Voir
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-4 text-center text-gray-400 text-sm">
                            Aucun ticket récent
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Actions rapides --}}
    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Actions Rapides</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.support.tickets.index', ['status' => 'open']) }}"
               class="flex flex-col items-center p-4 bg-gray-700/50 rounded-lg hover:bg-gray-700 transition-colors">
                <span class="text-2xl mb-2">📂</span>
                <span class="text-white text-sm font-medium">Tickets Ouverts</span>
                <span class="text-gray-400 text-xs">{{ $stats['open_tickets'] }}</span>
            </a>

            <a href="{{ route('admin.support.tickets.index', ['priority' => 'urgent']) }}"
               class="flex flex-col items-center p-4 bg-gray-700/50 rounded-lg hover:bg-gray-700 transition-colors">
                <span class="text-2xl mb-2">🚨</span>
                <span class="text-white text-sm font-medium">Urgents</span>
                <span class="text-gray-400 text-xs">{{ $stats['urgent_tickets'] }}</span>
            </a>

            <a href="{{ route('admin.support.tickets.index', ['assigned_to' => auth()->id()]) }}"
               class="flex flex-col items-center p-4 bg-gray-700/50 rounded-lg hover:bg-gray-700 transition-colors">
                <span class="text-2xl mb-2">👤</span>
                <span class="text-white text-sm font-medium">Mes Tickets</span>
                <span class="text-gray-400 text-xs">{{ $myTickets->count() }}</span>
            </a>

            <a href="{{ route('admin.support.categories.index') }}"
               class="flex flex-col items-center p-4 bg-gray-700/50 rounded-lg hover:bg-gray-700 transition-colors">
                <span class="text-2xl mb-2">🏷️</span>
                <span class="text-white text-sm font-medium">Catégories</span>
                <span class="text-gray-400 text-xs">Gérer</span>
            </a>
        </div>
    </div>
</div>
@endsection
