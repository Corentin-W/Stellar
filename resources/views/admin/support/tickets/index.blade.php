{{-- resources/views/admin/support/tickets/index.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Admin - Gestion des Tickets')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Gestion des Tickets</h1>
            <p class="text-gray-300">Administration du système de support</p>
        </div>
        <div class="flex items-center space-x-4 mt-4 sm:mt-0">
            <a href="{{ route('admin.support.dashboard') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-300">
                Dashboard
            </a>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6 mb-8">
        <form method="GET" action="{{ route('admin.support.tickets.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Recherche --}}
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Recherche</label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Numéro, sujet, nom ou email..."
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                {{-- Statut --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Statut</label>
                    <select name="status"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Tous les statuts</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Ouvert</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                        <option value="waiting_user" {{ request('status') === 'waiting_user' ? 'selected' : '' }}>En attente utilisateur</option>
                        <option value="waiting_admin" {{ request('status') === 'waiting_admin' ? 'selected' : '' }}>En attente admin</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolu</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Fermé</option>
                    </select>
                </div>

                {{-- Priorité --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Priorité</label>
                    <select name="priority"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Toutes priorités</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Élevée</option>
                        <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normale</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Faible</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Catégorie --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Catégorie</label>
                    <select name="category"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Toutes catégories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Assigné à --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Assigné à</label>
                    <select name="assigned_to"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Tous les agents</option>
                        <option value="0" {{ request('assigned_to') === '0' ? 'selected' : '' }}>Non assigné</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ request('assigned_to') == $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Date de début --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Date de début</label>
                    <input type="date"
                           name="date_from"
                           value="{{ request('date_from') }}"
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                {{-- Date de fin --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Date de fin</label>
                    <input type="date"
                           name="date_to"
                           value="{{ request('date_to') }}"
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
            </div>

            <div class="flex justify-between items-center pt-4">
                <div class="flex items-center space-x-2">
                    <button type="submit"
                            class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-300">
                        Filtrer
                    </button>
                    <a href="{{ route('admin.support.tickets.index') }}"
                       class="px-6 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition duration-300">
                        Réinitialiser
                    </a>
                </div>

                {{-- Tri --}}
                <div class="flex items-center space-x-2">
                    <select name="sort_by"
                            class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="created_at" {{ request('sort_by', 'created_at') === 'created_at' ? 'selected' : '' }}>Date création</option>
                        <option value="last_reply_at" {{ request('sort_by') === 'last_reply_at' ? 'selected' : '' }}>Dernière réponse</option>
                        <option value="priority" {{ request('sort_by') === 'priority' ? 'selected' : '' }}>Priorité</option>
                        <option value="status" {{ request('sort_by') === 'status' ? 'selected' : '' }}>Statut</option>
                    </select>
                    <select name="sort_order"
                            class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="desc" {{ request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>Décroissant</option>
                        <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Croissant</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    {{-- Liste des tickets --}}
    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700">
        @if($tickets->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Ticket
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Utilisateur
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Catégorie
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Priorité
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Statut
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Assigné à
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Créé
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($tickets as $ticket)
                            <tr class="hover:bg-gray-700/30 transition-colors
                                {{ $ticket->priority === 'urgent' ? 'bg-red-500/10' : '' }}
                                {{ $ticket->priority === 'high' ? 'bg-orange-500/10' : '' }}">

                                {{-- Informations du ticket --}}
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-white font-medium text-sm">{{ Str::limit($ticket->subject, 50) }}</div>
                                        <div class="text-gray-400 text-xs">{{ $ticket->ticket_number }}</div>
                                    </div>
                                </td>

                                {{-- Utilisateur --}}
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-white text-sm">{{ $ticket->user->name }}</div>
                                        <div class="text-gray-400 text-xs">{{ $ticket->user->email }}</div>
                                    </div>
                                </td>

                                {{-- Catégorie --}}
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                          style="background-color: {{ $ticket->category->color }}20; color: {{ $ticket->category->color }};">
                                        {{ $ticket->category->name }}
                                    </span>
                                </td>

                                {{-- Priorité --}}
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        {{ $ticket->priority === 'urgent' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $ticket->priority === 'high' ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $ticket->priority === 'normal' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $ticket->priority === 'low' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>

                                {{-- Statut --}}
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        {{ $ticket->status === 'open' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $ticket->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $ticket->status === 'waiting_user' ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $ticket->status === 'waiting_admin' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $ticket->status === 'resolved' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $ticket->status === 'closed' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </td>

                                {{-- Assigné à --}}
                                <td class="px-6 py-4">
                                    @if($ticket->assignedTo)
                                        <div class="text-white text-sm">{{ $ticket->assignedTo->name }}</div>
                                    @else
                                        <div class="text-gray-400 text-sm italic">Non assigné</div>
                                    @endif
                                </td>

                                {{-- Date de création --}}
                                <td class="px-6 py-4">
                                    <div class="text-gray-300 text-sm">{{ $ticket->created_at->format('d/m/Y') }}</div>
                                    <div class="text-gray-400 text-xs">{{ $ticket->created_at->diffForHumans() }}</div>
                                </td>

                                {{-- Actions --}}
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

            {{-- Pagination --}}
            @if($tickets->hasPages())
                <div class="px-6 py-4 border-t border-gray-700">
                    {{ $tickets->links() }}
                </div>
            @endif
        @else
            <div class="py-12 text-center">
                <div class="text-gray-400">
                    <div class="text-4xl mb-4">🎫</div>
                    <div class="text-lg mb-2">Aucun ticket trouvé</div>
                    <div class="text-sm">Essayez de modifier vos filtres de recherche</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Légende --}}
    <div class="mt-8 bg-blue-500/20 border border-blue-500 rounded-lg p-4">
        <h3 class="text-blue-100 font-medium mb-2">Légende des couleurs</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div class="flex items-center">
                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                <span class="text-blue-100">Priorité Urgente</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-orange-500 rounded-full mr-2"></div>
                <span class="text-blue-100">Priorité Élevée</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                <span class="text-blue-100">Priorité Normale</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-gray-500 rounded-full mr-2"></div>
                <span class="text-blue-100">Priorité Faible</span>
            </div>
        </div>
    </div>
</div>
@endsection
