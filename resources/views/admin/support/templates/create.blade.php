{{-- resources/views/admin/support/templates/create.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Créer un Template - Admin Support')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Créer un Template</h1>
            <p class="text-gray-300">Nouveau modèle de réponse prédéfinie</p>
        </div>
        <div class="flex items-center space-x-4 mt-4 sm:mt-0">
            <a href="{{ route('admin.support.templates.index') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-300">
                Retour à la liste
            </a>
        </div>
    </div>

    {{-- Messages d'erreur --}}
    @if($errors->any())
        <div class="bg-red-500/20 border border-red-500 text-red-100 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulaire --}}
    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
        <form method="POST" action="{{ route('admin.support.templates.store') }}" class="space-y-6">
            @csrf

            {{-- Nom du template --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                    Nom du template <span class="text-red-400">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="Ex: Réponse de bienvenue"
                       required>
                <p class="text-gray-400 text-sm mt-1">Nom descriptif pour identifier ce template</p>
            </div>

            {{-- Catégorie --}}
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-300 mb-2">
                    Catégorie associée
                </label>
                <select name="category_id"
                        id="category_id"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-gray-400 text-sm mt-1">Optionnel : associer ce template à une catégorie spécifique</p>
            </div>

            {{-- Sujet --}}
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-300 mb-2">
                    Sujet prédéfini
                </label>
                <input type="text"
                       id="subject"
                       name="subject"
                       value="{{ old('subject') }}"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="Ex: Re: Votre demande a été traitée">
                <p class="text-gray-400 text-sm mt-1">Optionnel : sujet automatique lors de l'utilisation du template</p>
            </div>

            {{-- Contenu du template --}}
            <div>
                <label for="content" class="block text-sm font-medium text-gray-300 mb-2">
                    Contenu du template <span class="text-red-400">*</span>
                </label>
                <textarea id="content"
                          name="content"
                          rows="12"
                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                          placeholder="Bonjour {nom},

Merci pour votre message concernant {sujet}.

Cordialement,
L'équipe support"
                          required>{{ old('content') }}</textarea>
                <p class="text-gray-400 text-sm mt-1">Utilisez des variables comme {nom}, {email}, {sujet} pour personnaliser automatiquement</p>
            </div>

            {{-- Variables disponibles --}}
            <div class="bg-blue-500/20 border border-blue-500 rounded-lg p-4">
                <h3 class="text-blue-100 font-medium mb-2">Variables disponibles</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                    <div class="flex items-center">
                        <code class="bg-blue-500/30 px-2 py-1 rounded text-blue-100 mr-2">{nom}</code>
                        <span class="text-blue-100">Nom de l'utilisateur</span>
                    </div>
                    <div class="flex items-center">
                        <code class="bg-blue-500/30 px-2 py-1 rounded text-blue-100 mr-2">{email}</code>
                        <span class="text-blue-100">Email de l'utilisateur</span>
                    </div>
                    <div class="flex items-center">
                        <code class="bg-blue-500/30 px-2 py-1 rounded text-blue-100 mr-2">{sujet}</code>
                        <span class="text-blue-100">Sujet du ticket</span>
                    </div>
                    <div class="flex items-center">
                        <code class="bg-blue-500/30 px-2 py-1 rounded text-blue-100 mr-2">{numero}</code>
                        <span class="text-blue-100">Numéro du ticket</span>
                    </div>
                    <div class="flex items-center">
                        <code class="bg-blue-500/30 px-2 py-1 rounded text-blue-100 mr-2">{date}</code>
                        <span class="text-blue-100">Date du jour</span>
                    </div>
                    <div class="flex items-center">
                        <code class="bg-blue-500/30 px-2 py-1 rounded text-blue-100 mr-2">{agent}</code>
                        <span class="text-blue-100">Nom de l'agent</span>
                    </div>
                </div>
            </div>

            {{-- Prévisualisation --}}
            <div class="border-t border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-white mb-4">Prévisualisation</h3>
                <div class="bg-gray-700/50 rounded-lg p-4">
                    <div class="mb-4">
                        <div class="text-sm text-gray-400 mb-1">Sujet :</div>
                        <div id="preview-subject" class="text-white font-medium">
                            {{ old('subject') ?: 'Pas de sujet défini' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-400 mb-1">Contenu :</div>
                        <div id="preview-content" class="text-white whitespace-pre-wrap">
                            {{ old('content') ?: 'Contenu du template...' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Boutons --}}
            <div class="flex items-center justify-end space-x-4 pt-6">
                <a href="{{ route('admin.support.templates.index') }}"
                   class="px-6 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition duration-300">
                    Annuler
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-300">
                    Créer le Template
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const subjectInput = document.getElementById('subject');
    const contentInput = document.getElementById('content');
    const previewSubject = document.getElementById('preview-subject');
    const previewContent = document.getElementById('preview-content');

    function updatePreview() {
        previewSubject.textContent = subjectInput.value || 'Pas de sujet défini';
        previewContent.textContent = contentInput.value || 'Contenu du template...';
    }

    subjectInput.addEventListener('input', updatePreview);
    contentInput.addEventListener('input', updatePreview);

    // Boutons d'insertion de variables
    const variables = ['{nom}', '{email}', '{sujet}', '{numero}', '{date}', '{agent}'];

    function insertVariable(variable) {
        const textarea = contentInput;
        const cursorPos = textarea.selectionStart;
        const textBefore = textarea.value.substring(0, cursorPos);
        const textAfter = textarea.value.substring(cursorPos);

        textarea.value = textBefore + variable + textAfter;
        textarea.focus();
        textarea.setSelectionRange(cursorPos + variable.length, cursorPos + variable.length);

        updatePreview();
    }

    // Ajouter des boutons pour insérer des variables rapidement
    const variableButtons = document.createElement('div');
    variableButtons.className = 'flex flex-wrap gap-2 mt-2';

    variables.forEach(variable => {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = variable;
        button.className = 'px-2 py-1 bg-purple-600/20 text-purple-300 rounded text-sm hover:bg-purple-600/30 transition-colors';
        button.addEventListener('click', () => insertVariable(variable));
        variableButtons.appendChild(button);
    });

    contentInput.parentNode.insertBefore(variableButtons, contentInput.nextSibling);
});
</script>
@endsection
