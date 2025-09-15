{{-- resources/views/support/create.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Nouveau Ticket de Support')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- En-tête --}}
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <a href="{{ route('support.index') }}" class="text-purple-400 hover:text-purple-300 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-white">Nouveau Ticket de Support</h1>
        </div>
        <p class="text-gray-300">Décrivez votre problème ou votre demande en détail</p>
    </div>

    {{-- Formulaire --}}
    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-8">

        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-6 py-4 rounded-lg mb-6">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('support.store', ['locale' => app()->getLocale()]) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Catégorie --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Catégorie *</label>
                <select name="category_id" required
                        class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">Sélectionnez une catégorie</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                            @if($category->description) - {{ $category->description }} @endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Priorité --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Priorité *</label>
                <select name="priority" required
                        class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Faible</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>Élevée</option>
                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
                <p class="text-sm text-gray-400 mt-1">
                    Choisissez "Urgent" uniquement pour des problèmes critiques nécessitant une intervention immédiate.
                </p>
            </div>

            {{-- Sujet --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Sujet *</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required
                       placeholder="Résumez brièvement votre demande..."
                       class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            {{-- Message --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Description détaillée *</label>
                <textarea name="message" rows="8" required
                          placeholder="Décrivez votre problème ou votre demande en détail. Plus vous fournirez d'informations, plus nous pourrons vous aider rapidement."
                          class="w-full bg-gray-700 text-white rounded-lg border border-gray-600 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 resize-vertical">{{ old('message') }}</textarea>
            </div>

            {{-- Fichiers joints --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Fichiers joints (optionnel)</label>
                <div class="border-2 border-dashed border-gray-600 rounded-lg p-6 text-center">
                    <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt"
                           class="hidden" id="attachments">
                    <label for="attachments" class="cursor-pointer">
                        <svg class="w-12 h-12 text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-gray-400 mb-2">Cliquez pour sélectionner des fichiers</p>
                        <p class="text-sm text-gray-500">Maximum 5 fichiers, 10Mo chacun</p>
                        <p class="text-xs text-gray-500">Formats acceptés : JPG, PNG, PDF, DOC, DOCX, TXT</p>
                    </label>
                </div>
                <div id="selected-files" class="mt-4 space-y-2"></div>
            </div>

            {{-- Boutons --}}
            <div class="flex justify-between pt-6">
                <a href="{{ route('support.index') }}"
                   class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition duration-300">
                    Annuler
                </a>
                <button type="submit"
                        class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-8 py-3 rounded-lg hover:from-purple-700 hover:to-pink-700 transition duration-300">
                    Créer le ticket
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Script pour la gestion des fichiers --}}
<script>
document.getElementById('attachments').addEventListener('change', function(e) {
    const filesDiv = document.getElementById('selected-files');
    filesDiv.innerHTML = '';

    Array.from(e.target.files).forEach((file, index) => {
        const fileDiv = document.createElement('div');
        fileDiv.className = 'bg-gray-700 rounded-lg p-3 flex items-center justify-between';
        fileDiv.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-white text-sm">${file.name}</span>
                <span class="text-gray-400 text-xs ml-2">(${(file.size / 1024 / 1024).toFixed(2)} Mo)</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;
        filesDiv.appendChild(fileDiv);
    });
});
</script>
@endsection
