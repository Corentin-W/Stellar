{{-- resources/views/support/show.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- En-tête du ticket --}}
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <a href="{{ route('support.index', ['locale' => app()->getLocale()]) }}" class="text-purple-400 hover:text-purple-300 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-white">{{ $ticket->subject }}</h1>
                <p class="text-gray-300">Ticket #{{ $ticket->ticket_number }}</p>
            </div>
        </div>

        {{-- Informations du ticket --}}
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-sm text-gray-400">Statut</p>
                    <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium
                        {{ $ticket->status === 'open' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : '' }}
                        {{ $ticket->status === 'closed' ? 'bg-gray-500/10 text-gray-400 border border-gray-500/20' : '' }}
                        {{ $ticket->status === 'waiting_admin' ? 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20' : '' }}">
                        {{ $ticket->status_label }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Priorité</p>
                    <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium
                        {{ $ticket->priority === 'low' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : '' }}
                        {{ $ticket->priority === 'normal' ? 'bg-gray-500/10 text-gray-400 border border-gray-500/20' : '' }}
                        {{ $ticket->priority === 'high' ? 'bg-orange-500/10 text-orange-400 border border-orange-500/20' : '' }}
                        {{ $ticket->priority === 'urgent' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : '' }}">
                        {{ $ticket->priority_label }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Catégorie</p>
                    <p class="text-white">{{ $ticket->category->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Créé le</p>
                    <p class="text-white">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
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
        {{-- Conversation --}}
        <div class="lg:col-span-2">
            <div class="space-y-6">
                {{-- Messages --}}
                @foreach($ticket->messages as $message)
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                @if($message->user->avatar)
                                    <img src="{{ $message->user->avatar }}" alt="{{ $message->user->name }}" class="w-10 h-10 rounded-full">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center">
                                        <span class="text-white font-semibold">{{ substr($message->user->name, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <p class="font-semibold text-white">{{ $message->user->name }}</p>
                                    <span class="text-sm text-gray-400 ml-2">{{ $message->created_at->format('d/m/Y H:i') }}</span>
                                    @if($message->is_system)
                                        <span class="ml-2 px-2 py-1 bg-gray-600 text-xs rounded">Système</span>
                                    @endif
                                </div>
                                <div class="text-gray-300 prose prose-invert max-w-none">
                                    {!! nl2br(e($message->message)) !!}
                                </div>

                                {{-- Fichiers joints --}}
                                @if($message->attachmentFiles->count() > 0)
                                    <div class="mt-4 space-y-2">
                                        <p class="text-sm font-medium text-gray-400">Fichiers joints :</p>
                                        @foreach($message->attachmentFiles as $attachment)
                                            <a href="{{ route('support.attachment.download', ['locale' => app()->getLocale(), 'attachment' => $attachment->id]) }}"
                                               class="inline-flex items-center px-3 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition duration-300">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                {{ $attachment->original_filename }}
                                                <span class="ml-2 text-xs text-gray-400">({{ number_format($attachment->file_size / 1024, 1) }} Ko)</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Formulaire de réponse --}}
                @if($ticket->status !== 'closed')
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Ajouter une réponse</h3>

                        <form method="POST" action="{{ route('support.reply', ['locale' => app()->getLocale(), 'ticket' => $ticket->id]) }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf

                            {{-- Message --}}
                            <div>
                                <textarea name="message" rows="4" required
                                          placeholder="Tapez votre réponse..."
                                          class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-vertical">{{ old('message') }}</textarea>
                            </div>

                            {{-- Fichiers joints --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Fichiers joints (optionnel)</label>
                                <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt"
                                       class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <p class="text-xs text-gray-400 mt-1">Maximum 5 fichiers, 10Mo chacun</p>
                            </div>

                            {{-- Bouton d'envoi --}}
                            <div class="flex justify-end">
                                <button type="submit"
                                        class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-2 rounded-lg hover:from-purple-700 hover:to-pink-700 transition duration-300">
                                    Envoyer la réponse
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Actions --}}
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Actions</h3>

                @if($ticket->status !== 'closed')
                    <form method="POST" action="{{ route('support.close', ['locale' => app()->getLocale(), 'ticket' => $ticket->id]) }}" class="mb-3">
                        @csrf
                        <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir fermer ce ticket ?')"
                                class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300">
                            Fermer le ticket
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('support.reopen', ['locale' => app()->getLocale(), 'ticket' => $ticket->id]) }}" class="mb-3">
                        @csrf
                        <button type="submit"
                                class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-300">
                            Rouvrir le ticket
                        </button>
                    </form>
                @endif
            </div>

            {{-- Informations supplémentaires --}}
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Informations</h3>

                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-400">Dernière réponse</p>
                        <p class="text-white">{{ $ticket->last_reply_at ? $ticket->last_reply_at->format('d/m/Y H:i') : 'Aucune' }}</p>
                    </div>

                    @if($ticket->closed_at)
                    <div>
                        <p class="text-gray-400">Fermé le</p>
                        <p class="text-white">{{ $ticket->closed_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif

                    @if($ticket->assignedTo)
                    <div>
                        <p class="text-gray-400">Assigné à</p>
                        <p class="text-white">{{ $ticket->assignedTo->name }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
