{{-- resources/views/support/show.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Ticket ' . $ticket->ticket_number)

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- En-tête --}}
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <a href="{{ route('support.index') }}" class="text-purple-400 hover:text-purple-300 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="flex-1">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-white">{{ $ticket->subject }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $ticket->status_color }}">
                        {{ $ticket->status_label }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $ticket->priority_color }}">
                        {{ $ticket->priority_label }}
                    </span>
                </div>
                <div class="flex items-center space-x-6 mt-2 text-sm text-gray-400">
                    <span>Ticket #{{ $ticket->ticket_number }}</span>
                    <span>Créé le {{ $ticket->created_at->format('d/m/Y à H:i') }}</span>
                    <span class="flex items-center">
                        <span class="w-2 h-2 rounded-full mr-2" style="background-color: {{ $ticket->category->color }};"></span>
                        {{ $ticket->category->name }}
                    </span>
                    @if($ticket->assignedTo)
                        <span>Assigné à {{ $ticket->assignedTo->name }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center space-x-4">
            @if($ticket->canBeRepliedTo())
                <a href="#reply" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-2 rounded-lg hover:from-purple-700 hover:to-pink-700 transition duration-300">
                    Répondre
                </a>
            @endif

            @if($ticket->isResolved() || $ticket->isOpen() || $ticket->isInProgress())
                <form method="POST" action="{{ route('support.close', $ticket->id) }}" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir fermer ce ticket ?')"
                            class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-300">
                        Fermer le ticket
                    </button>
                </form>
            @endif

            @if($ticket->isClosed() || $ticket->isResolved())
                <form method="POST" action="{{ route('support.reopen', $ticket->id) }}" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir rouvrir ce ticket ?')"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                        Rouvrir le ticket
                    </button>
                </form>
            @endif
        </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Messages --}}
        <div class="lg:col-span-2">
            <div class="space-y-6">

                @foreach($ticket->messages as $message)
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 overflow-hidden">

                        {{-- En-tête du message --}}
                        <div class="px-6 py-4 border-b border-gray-600 flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-semibold">
                                    {{ substr($message->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium text-white">{{ $message->user->name }}</span>
                                        @if($message->isFromAdmin())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                Support
                                            </span>
                                        @endif
                                        @if($message->is_system)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Système
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-400">{{ $message->created_at->format('d/m/Y à H:i') }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Contenu du message --}}
                        <div class="px-6 py-4">
                            <div class="text-gray-300 leading-relaxed">
                                {!! nl2br(e($message->message)) !!}
                            </div>

                            {{-- Fichiers joints --}}
                            @if($message->attachmentFiles->isNotEmpty())
                                <div class="mt-4 pt-4 border-t border-gray-600">
                                    <h4 class="text-sm font-medium text-gray-300 mb-3">Fichiers joints :</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        @foreach($message->attachmentFiles as $attachment)
                                            <div class="bg-gray-700/50 rounded-lg p-3 flex items-center justify-between">
                                                <div class="flex items-center min-w-0">
                                                    @if($attachment->isImage())
                                                        <svg class="w-5 h-5 text-green-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-5 h-5 text-blue-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    @endif
                                                    <div class="min-w-0">
                                                        <div class="text-white text-sm truncate">{{ $attachment->original_filename }}</div>
                                                        <div class="text-gray-400 text-xs">{{ $attachment->formatted_size }}</div>
                                                    </div>
                                                </div>
                                                <a href="{{ route('support.attachment.download', $attachment->id) }}"
                                                   class="ml-2 text-purple-400 hover:text-purple-300 flex-shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- Formulaire de réponse --}}
                @if($ticket->canBeRepliedTo())
                    <div id="reply" class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
                        <h3 class="text-xl font-semibold text-white mb-4">Ajouter une réponse</h3>

                        <form method="POST" action="{{ route('support.reply', $ticket->id) }}" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-6">
                                <textarea name="message" rows="6" required
                                          placeholder="Tapez votre réponse ici..."
                                          class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-vertical"></textarea>
                            </div>

                            {{-- Fichiers joints --}}
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Fichiers joints (optionnel)</label>
                                <div class="border-2 border-dashed border-gray-600 rounded-lg p-4 text-center">
                                    <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt"
                                           class="hidden" id="reply-attachments">
                                    <label for="reply-attachments" class="cursor-pointer">
                                        <svg class="w-8 h-8 text-gray-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        <p class="text-gray-400 text-sm">Cliquez pour ajouter des fichiers</p>
                                    </label>
                                </div>
                                <div id="reply-selected-files" class="mt-3 space-y-2"></div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                        class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-3 rounded-lg hover:from-purple-700 hover:to-pink-700 transition duration-300">
                                    Envoyer la réponse
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6 text-center">
                        <svg class="w-12 h-12 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-300 mb-2">Ticket fermé</h3>
                        <p class="text-gray-400">Ce ticket ne peut plus recevoir de nouvelles réponses.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar avec informations --}}
        <div class="lg:col-span-1">
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Informations du ticket</h3>

                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-gray-400 mb-1">Statut</div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $ticket->status_color }}">
                            {{ $ticket->status_label }}
                        </span>
                    </div>

                    <div>
                        <div class="text-sm text-gray-400 mb-1">Priorité</div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $ticket->priority_color }}">
                            {{ $ticket->priority_label }}
                        </span>
                    </div>

                    <div>
                        <div class="text-sm text-gray-400 mb-1">Catégorie</div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $ticket->category->color }};"></span>
                            <span class="text-white">{{ $ticket->category->name }}</span>
                        </div>
                    </div>

                    @if($ticket->assignedTo)
                        <div>
                            <div class="text-sm text-gray-400 mb-1">Assigné à</div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-xs font-semibold mr-2">
                                    {{ substr($ticket->assignedTo->name, 0, 1) }}
                                </div>
                                <span class="text-white">{{ $ticket->assignedTo->name }}</span>
                            </div>
                        </div>
                    @endif

                    <div>
                        <div class="text-sm text-gray-400 mb-1">Créé le</div>
                        <div class="text-white">{{ $ticket->created_at->format('d/m/Y à H:i') }}</div>
                    </div>

                    @if($ticket->last_reply_at)
                        <div>
                            <div class="text-sm text-gray-400 mb-1">Dernière réponse</div>
                            <div class="text-white">{{ $ticket->last_reply_at->diffForHumans() }}</div>
                        </div>
                    @endif

                    @if($ticket->resolved_at)
                        <div>
                            <div class="text-sm text-gray-400 mb-1">Résolu le</div>
                            <div class="text-white">{{ $ticket->resolved_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    @endif

                    @if($ticket->closed_at)
                        <div>
                            <div class="text-sm text-gray-400 mb-1">Fermé le</div>
                            <div class="text-white">{{ $ticket->closed_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Conseils d'utilisation --}}
            <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-6 mt-6">
                <h4 class="text-blue-300 font-semibold mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Conseils
                </h4>
                <div class="text-blue-200 text-sm space-y-2">
                    <p>• Soyez précis dans vos réponses</p>
                    <p>• Joignez des captures d'écran si nécessaire</p>
                    <p>• Répondez rapidement pour accélérer la résolution</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script pour la gestion des fichiers de réponse --}}
<script>
document.getElementById('reply-attachments').addEventListener('change', function(e) {
    const filesDiv = document.getElementById('reply-selected-files');
    filesDiv.innerHTML = '';

    Array.from(e.target.files).forEach((file, index) => {
        const fileDiv = document.createElement('div');
        fileDiv.className = 'bg-gray-700 rounded-lg p-3 flex items-center justify-between';
        fileDiv.innerHTML = `
            <div class="flex items-center min-w-0">
                <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                <div class="min-w-0">
                    <div class="text-white text-sm truncate">${file.name}</div>
                    <div class="text-gray-400 text-xs">${(file.size / 1024 / 1024).toFixed(2)} Mo</div>
                </div>
            </div>
            <button type="button" onclick="removeFile(this, ${index})" class="text-red-400 hover:text-red-300 ml-2 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;
        filesDiv.appendChild(fileDiv);
    });
});

function removeFile(button, index) {
    // Supprimer visuellement le fichier
    button.closest('div').remove();

    // Note: Pour vraiment supprimer le fichier de l'input, il faudrait une solution plus complexe
    // car on ne peut pas modifier directement les FileList. Pour une version de production,
    // considérez l'utilisation d'une bibliothèque comme Dropzone.js
}
</script>
@endsection
