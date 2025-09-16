{{-- resources/views/admin/support/tickets/show.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Ticket ' . $ticket->ticket_number . ' - Admin Support')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- En-tête --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8">
        <div>
            <div class="flex items-center space-x-4 mb-2">
                <h1 class="text-3xl font-bold text-white">{{ $ticket->subject }}</h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    {{ $ticket->priority === 'urgent' ? 'bg-red-100 text-red-800' : '' }}
                    {{ $ticket->priority === 'high' ? 'bg-orange-100 text-orange-800' : '' }}
                    {{ $ticket->priority === 'normal' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $ticket->priority === 'low' ? 'bg-gray-100 text-gray-800' : '' }}">
                    {{ ucfirst($ticket->priority) }}
                </span>
            </div>
            <p class="text-gray-300">Ticket #{{ $ticket->ticket_number }}</p>
        </div>
        <div class="flex items-center space-x-4 mt-4 lg:mt-0">
            <a href="{{ route('admin.support.tickets.index') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-300">
                Retour à la liste
            </a>
        </div>
    </div>

    {{-- Messages flash --}}
    @if(session('success'))
        <div class="bg-green-500/20 border border-green-500 text-green-100 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-500/20 border border-red-500 text-red-100 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Colonne principale : Messages --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Messages du ticket --}}
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700">
                <div class="p-6 border-b border-gray-700">
                    <h2 class="text-xl font-semibold text-white">Conversation</h2>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    @forelse($ticket->messages as $message)
                        <div class="p-6 border-b border-gray-700 last:border-b-0
                            {{ $message->is_internal ? 'bg-yellow-500/10' : '' }}
                            {{ $message->is_system ? 'bg-blue-500/10' : '' }}">

                            <div class="flex items-start space-x-4">
                                {{-- Avatar/Icône --}}
                                <div class="flex-shrink-0">
                                    @if($message->is_system)
                                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm">🤖</span>
                                        </div>
                                    @elseif($message->user && $message->user->admin)
                                        <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm">👨‍💼</span>
                                        </div>
                                    @else
                                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm">👤</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Contenu du message --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-2">
                                            <h4 class="text-white font-medium">{{ $message->author_name }}</h4>
                                            @if($message->is_internal)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Interne
                                                </span>
                                            @endif
                                            @if($message->is_system)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Système
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-gray-400 text-sm">{{ $message->created_at->format('d/m/Y H:i') }}</span>
                                    </div>

                                    <div class="text-gray-300 whitespace-pre-wrap">{{ $message->message }}</div>

                                    {{-- Fichiers joints --}}
                                    @if($message->attachmentFiles->count() > 0)
                                        <div class="mt-4">
                                            <h5 class="text-sm font-medium text-gray-400 mb-2">Fichiers joints :</h5>
                                            <div class="space-y-2">
                                                @foreach($message->attachmentFiles as $attachment)
                                                    <div class="flex items-center space-x-2 p-2 bg-gray-700/50 rounded">
                                                        <span class="text-lg">{{ $attachment->file_icon }}</span>
                                                        <div class="flex-1">
                                                            <div class="text-white text-sm">{{ $attachment->original_filename }}</div>
                                                            <div class="text-gray-400 text-xs">{{ $attachment->file_size_human }}</div>
                                                        </div>
                                                        <a href="{{ $attachment->download_url }}"
                                                           class="text-blue-400 hover:text-blue-300 text-sm">
                                                            Télécharger
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-400">
                            Aucun message pour ce ticket
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Formulaire de réponse --}}
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700">
                <div class="p-6 border-b border-gray-700">
                    <h2 class="text-xl font-semibold text-white">Répondre au ticket</h2>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.support.tickets.reply', $ticket) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        {{-- Templates rapides --}}
                        @if($templates->count() > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Templates rapides</label>
                                <select id="template-select"
                                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="">Choisir un template...</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Message --}}
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-300 mb-2">Message</label>
                            <textarea id="message"
                                      name="message"
                                      rows="6"
                                      class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                      placeholder="Votre réponse..."
                                      required>{{ old('message') }}</textarea>
                        </div>

                        {{-- Options --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                           name="is_internal"
                                           value="1"
                                           class="w-4 h-4 text-purple-600 bg-gray-700 border-gray-600 rounded focus:ring-purple-500">
                                    <span class="ml-2 text-gray-300">Message interne (non visible au client)</span>
                                </label>
                            </div>

                            <div>
                                <label for="change_status" class="block text-sm font-medium text-gray-300 mb-2">Changer le statut</label>
                                <select name="change_status"
                                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="">Conserver le statut actuel</option>
                                    <option value="open">Ouvert</option>
                                    <option value="in_progress">En cours</option>
                                    <option value="waiting_user">En attente utilisateur</option>
                                    <option value="waiting_admin">En attente admin</option>
                                    <option value="resolved">Résolu</option>
                                    <option value="closed">Fermé</option>
                                </select>
                            </div>
                        </div>

                        {{-- Fichiers joints --}}
                        <div>
                            <label for="attachments" class="block text-sm font-medium text-gray-300 mb-2">Fichiers joints</label>
                            <input type="file"
                                   id="attachments"
                                   name="attachments[]"
                                   multiple
                                   accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt"
                                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-white file:bg-purple-600 hover:file:bg-purple-700">
                            <p class="text-gray-400 text-sm mt-1">Max 5 fichiers, 10MB chacun. Formats: JPG, PNG, PDF, DOC, TXT</p>
                        </div>

                        {{-- Bouton d'envoi --}}
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-300">
                                Envoyer la réponse
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Colonne latérale : Informations du ticket --}}
        <div class="space-y-6">

            {{-- Informations du ticket --}}
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700">
                <div class="p-6 border-b border-gray-700">
                    <h3 class="text-lg font-semibold text-white">Informations</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-gray-400">Statut</div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $ticket->status === 'open' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $ticket->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $ticket->status === 'waiting_user' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $ticket->status === 'waiting_admin' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $ticket->status === 'resolved' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $ticket->status === 'closed' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </div>

                    <div>
                        <div class="text-sm text-gray-400">Utilisateur</div>
                        <div class="text-white">{{ $ticket->user->name }}</div>
                        <div class="text-gray-300 text-sm">{{ $ticket->user->email }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-400">Catégorie</div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium"
                              style="background-color: {{ $ticket->category->color }}20; color: {{ $ticket->category->color }};">
                            {{ $ticket->category->name }}
                        </span>
                    </div>

                    <div>
                        <div class="text-sm text-gray-400">Assigné à</div>
                        <div class="text-white">{{ $ticket->assignedTo?->name ?? 'Non assigné' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-400">Créé le</div>
                        <div class="text-white">{{ $ticket->created_at->format('d/m/Y à H:i') }}</div>
                    </div>

                    @if($ticket->last_reply_at)
                        <div>
                            <div class="text-sm text-gray-400">Dernière réponse</div>
                            <div class="text-white">{{ $ticket->last_reply_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions rapides --}}
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700">
                <div class="p-6 border-b border-gray-700">
                    <h3 class="text-lg font-semibold text-white">Actions</h3>
                </div>
                <div class="p-6 space-y-4">

                    {{-- Assigner --}}
                    <form method="POST" action="{{ route('admin.support.tickets.assign', $ticket) }}">
                        @csrf
                        <label class="block text-sm font-medium text-gray-300 mb-2">Assigner à</label>
                        <div class="flex space-x-2">
                            <select name="assigned_to"
                                    class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Non assigné</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}"
                                            {{ $ticket->assigned_to == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">
                                OK
                            </button>
                        </div>
                    </form>

                    {{-- Changer priorité --}}
                    <form method="POST" action="{{ route('admin.support.tickets.change-priority', $ticket) }}">
                        @csrf
                        <label class="block text-sm font-medium text-gray-300 mb-2">Priorité</label>
                        <div class="flex space-x-2">
                            <select name="priority"
                                    class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Faible</option>
                                <option value="normal" {{ $ticket->priority === 'normal' ? 'selected' : '' }}>Normale</option>
                                <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>Élevée</option>
                                <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            <button type="submit"
                                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition duration-300">
                                OK
                            </button>
                        </div>
                    </form>

                    {{-- Changer catégorie --}}
                    <form method="POST" action="{{ route('admin.support.tickets.change-category', $ticket) }}">
                        @csrf
                        <label class="block text-sm font-medium text-gray-300 mb-2">Catégorie</label>
                        <div class="flex space-x-2">
                            <select name="category_id"
                                    class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                            {{ $ticket->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-300">
                                OK
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Historique --}}
            @if($ticket->history->count() > 0)
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700">
                    <div class="p-6 border-b border-gray-700">
                        <h3 class="text-lg font-semibold text-white">Historique</h3>
                    </div>
                    <div class="max-h-64 overflow-y-auto">
                        @foreach($ticket->history as $entry)
                            <div class="p-4 border-b border-gray-700 last:border-b-0">
                                <div class="flex items-start space-x-3">
                                    <div class="w-2 h-2 bg-purple-500 rounded-full mt-2"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-white text-sm">{{ $entry->formatted_description }}</div>
                                        <div class="text-gray-400 text-xs mt-1">
                                            {{ $entry->author_name }} - {{ $entry->created_at->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des templates
    const templateSelect = document.getElementById('template-select');
    const messageTextarea = document.getElementById('message');

    if (templateSelect) {
        templateSelect.addEventListener('change', function() {
            if (this.value) {
                fetch(`/admin/support/templates/${this.value}/content`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.content) {
                            messageTextarea.value = data.content;
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                    });
            }
        });
    }
});
</script>
@endsection
