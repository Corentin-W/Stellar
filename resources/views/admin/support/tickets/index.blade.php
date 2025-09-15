{{-- resources/views/admin/support/tickets/index.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Gestion des Tickets de Support')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Gestion des Tickets</h1>
            <p class="text-gray-300">{{ $tickets->total() }} tickets au total</p>
        </div>
        <div class="flex items-center space-x-4 mt-4 sm:mt-0">
            <a href="{{ route('admin.support.dashboard') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-300">
                Dashboard
            </a>
            <a href="{{ route('admin.support.reports.index') }}"
               class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-300">
                Rapports
            </a>
        </div>
    </div>

    {{-- Filtres avancés --}}
    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4">

            {{-- Recherche --}}
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Numéro, sujet, utilisateur..."
                       class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            {{-- Statut --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Statut</label>
                <select name="status" class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
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
                <select name="priority" class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">Toutes les priorités</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Élevée</option>
                    <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Faible</option>
                </select>
            </div>

            {{-- Catégorie --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Catégorie</label>
                <select name="category" class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Agent assigné --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Assigné à</label>
                <select name="assigned_to" class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">Tous les agents</option>
                    <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>Non assigné</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ request('assigned_to') == $admin->id ? 'selected' : '' }}>
                            {{ $admin->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Date de création (début) --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Date de</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            {{-- Date de création (fin) --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Date à</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            {{-- Tri --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Trier par</label>
                <select name="sort_by" class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Date de création</option>
                    <option value="last_reply_at" {{ request('sort_by') === 'last_reply_at' ? 'selected' : '' }}>Dernière réponse</option>
                    <option value="priority" {{ request('sort_by') === 'priority' ? 'selected' : '' }}>Priorité</option>
                    <option value="status" {{ request('sort_by') === 'status' ? 'selected' : '' }}>Statut</option>
                </select>
            </div>

            {{-- Ordre de tri --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Ordre</label>
                <select name="sort_order" class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Décroissant</option>
                    <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Croissant</option>
                </select>
            </div>

            {{-- Boutons --}}
            <div class="lg:col-span-2 xl:col-span-1 flex space-x-2">
                <button type="submit" class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-300">
                    Filtrer
                </button>
                <a href="{{ route('admin.support.tickets.index') }}" class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-300 text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Messages --}}
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

    {{-- Actions en lot --}}
    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-4 mb-6" id="bulk-actions" style="display: none;">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <span class="text-white font-medium">Actions en lot :</span>
                <button type="button" onclick="bulkAssign()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-300 text-sm">
                    Assigner
                </button>
                <button type="button" onclick="bulkChangeStatus()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 text-sm">
                    Changer statut
                </button>
                <button type="button" onclick="bulkChangePriority()" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition duration-300 text-sm">
                    Changer priorité
                </button>
            </div>
            <div class="flex items-center space-x-2">
                <span id="selected-count" class="text-gray-300 text-sm"></span>
                <button type="button" onclick="clearSelection()" class="text-gray-400 hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Liste des tickets --}}
    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 overflow-hidden">

        @if($tickets->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-700/50 border-b border-gray-600">
                        <tr>
                            <th class="px-4 py-4 w-8">
                                <input type="checkbox" id="select-all" class="rounded bg-gray-700 border-gray-600 text-purple-600 focus:ring-purple-500">
                            </th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Ticket</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Utilisateur</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Sujet</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Catégorie</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Priorité</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Statut</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Assigné à</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Dernière activité</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-600">
                        @foreach($tickets as $ticket)
                            <tr class="hover:bg-gray-700/30 transition-colors" data-ticket-id="{{ $ticket->id }}">
                                <td class="px-4 py-4">
                                    <input type="checkbox" class="ticket-checkbox rounded bg-gray-700 border-gray-600 text-purple-600 focus:ring-purple-500" value="{{ $ticket->id }}">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-mono text-sm text-purple-400">{{ $ticket->ticket_number }}</div>
                                    <div class="text-xs text-gray-400">{{ $ticket->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white text-xs font-semibold mr-3">
                                            {{ substr($ticket->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-white font-medium">{{ $ticket->user->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $ticket->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-white font-medium">{{ Str::limit($ticket->subject, 40) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          style="background-color: {{ $ticket->category->color }}20; color: {{ $ticket->category->color }};">
                                        {{ $ticket->category->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->priority_color }}">
                                        {{ $ticket->priority_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->status_color }}">
                                        {{ $ticket->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($ticket->assignedTo)
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-xs font-semibold mr-2">
                                                {{ substr($ticket->assignedTo->name, 0, 1) }}
                                            </div>
                                            <span class="text-white text-sm">{{ $ticket->assignedTo->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">Non assigné</span>
                                    @endif
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
                                        <a href="{{ route('admin.support.tickets.show', $ticket->id) }}"
                                           class="text-purple-400 hover:text-purple-300 text-sm">
                                            Voir
                                        </a>
                                        <span class="text-gray-600">|</span>
                                        <button onclick="quickAssign({{ $ticket->id }})"
                                                class="text-blue-400 hover:text-blue-300 text-sm">
                                            Assigner
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($tickets->hasPages())
                <div class="px-6 py-4 border-t border-gray-600">
                    {{ $tickets->appends(request()->query())->links() }}
                </div>
            @endif

        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="text-xl font-medium text-gray-300 mb-2">Aucun ticket trouvé</h3>
                <p class="text-gray-400">Aucun ticket ne correspond aux critères de recherche.</p>
            </div>
        @endif
    </div>
</div>

{{-- Script pour la gestion des sélections multiples --}}
<script>
let selectedTickets = new Set();

// Sélectionner/déselectionner tous
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.ticket-checkbox');
    const bulkActions = document.getElementById('bulk-actions');

    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
        if (this.checked) {
            selectedTickets.add(parseInt(checkbox.value));
        } else {
            selectedTickets.delete(parseInt(checkbox.value));
        }
    });

    updateBulkActions();
});

// Gestion des sélections individuelles
document.querySelectorAll('.ticket-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            selectedTickets.add(parseInt(this.value));
        } else {
            selectedTickets.delete(parseInt(this.value));
        }
        updateBulkActions();

        // Mettre à jour le checkbox "select-all"
        const allCheckboxes = document.querySelectorAll('.ticket-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.ticket-checkbox:checked');
        document.getElementById('select-all').checked = allCheckboxes.length === checkedCheckboxes.length;
    });
});

function updateBulkActions() {
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');

    if (selectedTickets.size > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = `${selectedTickets.size} ticket(s) sélectionné(s)`;
    } else {
        bulkActions.style.display = 'none';
    }
}

function clearSelection() {
    selectedTickets.clear();
    document.querySelectorAll('.ticket-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('select-all').checked = false;
    updateBulkActions();
}

function quickAssign(ticketId) {
    // Implémentation de l'assignation rapide
    // Vous pouvez ouvrir une modal ou rediriger vers une page d'assignation
    window.location.href = `/admin/support/tickets/${ticketId}#assign`;
}

function bulkAssign() {
    if (selectedTickets.size === 0) return;

    // Implémentation de l'assignation en lot
    const ticketIds = Array.from(selectedTickets).join(',');
    // Ouvrir une modal ou rediriger avec les IDs
    console.log('Assigner les tickets:', ticketIds);
}

function bulkChangeStatus() {
    if (selectedTickets.size === 0) return;

    // Implémentation du changement de statut en lot
    const ticketIds = Array.from(selectedTickets).join(',');
    console.log('Changer le statut des tickets:', ticketIds);
}

function bulkChangePriority() {
    if (selectedTickets.size === 0) return;

    // Implémentation du changement de priorité en lot
    const ticketIds = Array.from(selectedTickets).join(',');
    console.log('Changer la priorité des tickets:', ticketIds);
}
</script>
@endsection
