{{-- resources/views/support/index.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Support - Mes Tickets')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Mes Tickets de Support</h1>
            <p class="text-gray-300">Gérez vos demandes d'assistance</p>
        </div>
        <a href="{{ route('support.create') }}"
           class="mt-4 sm:mt-0 bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-3 rounded-lg hover:from-purple-700 hover:to-pink-700 transition duration-300 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nouveau Ticket
        </a>
    </div>

    {{-- Filtres --}}
    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

            {{-- Recherche --}}
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-gray-300 mb-2">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Numéro ou sujet..."
                       class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            {{-- Statut --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Statut</label>
                <select name="status" class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">Tous les statuts</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Ouvert</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                    <option value="waiting_user" {{ request('status') === 'waiting_user' ? 'selected' : '' }}>En attente</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolu</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Fermé</option>
                </select>
            </div>

            {{-- Priorité --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Priorité</label>
                <select name="priority" class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">Toutes les priorités</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Faible</option>
                    <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Élevée</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>

            {{-- Catégorie --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Catégorie</label>
                <select name="category" class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">Toutes les catégories</option>
                    @if(isset($categories))
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- Boutons --}}
            <div class="md:col-span-4 flex space-x-4">
                <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition duration-300">
                    Filtrer
                </button>
                <a href="{{ route('support.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition duration-300">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    {{-- Messages de succès/erreur --}}
    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/20 text-green-400 px-6 py-4 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-6 py-4 rounded-lg mb-6">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Liste des tickets --}}
    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 overflow-hidden">

        @if(isset($tickets) && $tickets->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-700/50 border-b border-gray-600">
                        <tr>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Numéro</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Sujet</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Catégorie</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Priorité</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Statut</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Dernière réponse</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-600">
                        @foreach($tickets as $ticket)
                            <tr class="hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-mono text-sm text-purple-400">{{ $ticket->ticket_number ?? '#' . $ticket->id }}</div>
                                    <div class="text-xs text-gray-400">{{ $ticket->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-white font-medium">{{ Str::limit($ticket->subject, 50) }}</div>
                                    @if($ticket->messages && $ticket->messages->isNotEmpty())
                                        <div class="text-xs text-gray-400 mt-1">
                                            {{ Str::limit($ticket->messages->first()->message, 60) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($ticket->category)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              style="background-color: {{ $ticket->category->color }}20; color: {{ $ticket->category->color }};">
                                            {{ $ticket->category->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $ticket->priority === 'urgent' ? 'bg-red-100 text-red-800' :
                                           ($ticket->priority === 'high' ? 'bg-yellow-100 text-yellow-800' :
                                           ($ticket->priority === 'normal' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800')) }}">
                                        {{ ucfirst($ticket->priority ?? 'normal') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $ticket->status === 'open' ? 'bg-blue-100 text-blue-800' :
                                           ($ticket->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                                           ($ticket->status === 'resolved' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status ?? 'open')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-300">
                                    @if($ticket->last_reply_at)
                                        {{ $ticket->last_reply_at->diffForHumans() }}
                                    @else
                                        {{ $ticket->created_at->diffForHumans() }}
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('support.show', ['locale' => app()->getLocale(), 'ticket' => $ticket->id]) }}"
                                           class="text-purple-400 hover:text-purple-300 text-sm">
                                            Voir
                                        </a>
                                        @if(!in_array($ticket->status, ['closed']))
                                            <span class="text-gray-600">|</span>
                                            <a href="{{ route('support.show', ['locale' => app()->getLocale(), 'ticket' => $ticket->id]) }}#reply"
                                               class="text-blue-400 hover:text-blue-300 text-sm">
                                                Répondre
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(method_exists($tickets, 'hasPages') && $tickets->hasPages())
                <div class="px-6 py-4 border-t border-gray-600">
                    {{ $tickets->links() }}
                </div>
            @endif

        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-xl font-medium text-gray-300 mb-2">Aucun ticket trouvé</h3>
                <p class="text-gray-400 mb-6">Vous n'avez pas encore créé de ticket de support.</p>
                <a href="{{ route('support.create') }}"
                   class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-3 rounded-lg hover:from-purple-700 hover:to-pink-700 transition duration-300 inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Créer mon premier ticket
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
