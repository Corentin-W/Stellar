{{-- resources/views/admin/support/categories/edit.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Modifier une Catégorie - Admin Support')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- En-tête --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Modifier la Catégorie</h1>
            <p class="text-gray-300">{{ $category->name }}</p>
        </div>
        <div class="flex items-center space-x-4 mt-4 sm:mt-0">
            <a href="{{ route('admin.support.categories.index') }}"
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
        <form method="POST" action="{{ route('admin.support.categories.update', $category) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Nom de la catégorie --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                    Nom de la catégorie <span class="text-red-400">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $category->name) }}"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="Ex: Support Technique"
                       required>
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-gray-300 mb-2">
                    Description
                </label>
                <textarea id="description"
                          name="description"
                          rows="3"
                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                          placeholder="Description de la catégorie (optionnel)">{{ old('description', $category->description) }}</textarea>
            </div>

            {{-- Couleur --}}
            <div>
                <label for="color" class="block text-sm font-medium text-gray-300 mb-2">
                    Couleur <span class="text-red-400">*</span>
                </label>
                <div class="flex items-center space-x-4">
                    <input type="color"
                           id="color"
                           name="color"
                           value="{{ old('color', $category->color) }}"
                           class="w-16 h-12 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer">
                    <div class="flex-1">
                        <input type="text"
                               id="color_text"
                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               value="{{ old('color', $category->color) }}"
                               placeholder="#000000">
                    </div>
                </div>
                <p class="text-gray-400 text-sm mt-1">Couleur utilisée pour identifier la catégorie dans l'interface</p>
            </div>

            {{-- Ordre d'affichage --}}
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-300 mb-2">
                    Ordre d'affichage
                </label>
                <input type="number"
                       id="sort_order"
                       name="sort_order"
                       value="{{ old('sort_order', $category->sort_order) }}"
                       min="0"
                       class="w-32 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="0">
                <p class="text-gray-400 text-sm mt-1">Ordre d'affichage dans les listes (0 = premier)</p>
            </div>

            {{-- Informations sur les tickets --}}
            @if($category->tickets()->count() > 0)
                <div class="bg-blue-500/20 border border-blue-500 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <span class="text-white text-sm font-bold">ℹ️</span>
                        </div>
                        <h3 class="text-blue-100 font-medium">Tickets associés</h3>
                    </div>
                    <p class="text-blue-100 text-sm">
                        Cette catégorie contient <strong>{{ $category->tickets()->count() }} ticket(s)</strong>.
                        La modification de cette catégorie affectera tous ces tickets.
                    </p>
                </div>
            @endif

            {{-- Prévisualisation --}}
            <div class="border-t border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-white mb-4">Prévisualisation</h3>
                <div class="bg-gray-700/50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div id="preview-color" class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $category->color }};"></div>
                        <div>
                            <div id="preview-name" class="text-white font-medium">{{ $category->name }}</div>
                            <div id="preview-description" class="text-gray-300 text-sm">{{ $category->description ?: 'Aucune description' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Boutons --}}
            <div class="flex items-center justify-end space-x-4 pt-6">
                <a href="{{ route('admin.support.categories.index') }}"
                   class="px-6 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition duration-300">
                    Annuler
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-300">
                    Sauvegarder les Modifications
                </button>
            </div>
        </form>
    </div>

    {{-- Actions supplémentaires --}}
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Activer/Désactiver --}}
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
            <h3 class="text-lg font-medium text-white mb-2">Statut de la Catégorie</h3>
            <p class="text-gray-300 text-sm mb-4">
                {{ $category->is_active ? 'Cette catégorie est actuellement active et visible aux utilisateurs.' : 'Cette catégorie est désactivée et non visible aux utilisateurs.' }}
            </p>
            <form method="POST" action="{{ route('admin.support.categories.toggle-status', $category) }}" class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 {{ $category->is_active ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg transition duration-300">
                    {{ $category->is_active ? 'Désactiver' : 'Activer' }} la Catégorie
                </button>
            </form>
        </div>

        {{-- Supprimer --}}
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700 p-6">
            <h3 class="text-lg font-medium text-white mb-2">Zone de Danger</h3>
            @if($category->tickets()->count() == 0)
                <p class="text-gray-300 text-sm mb-4">
                    Supprimer définitivement cette catégorie. Cette action est irréversible.
                </p>
                <form method="POST" action="{{ route('admin.support.categories.destroy', $category) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-300"
                            onclick="return confirm('Êtes-vous absolument sûr de vouloir supprimer cette catégorie ? Cette action est irréversible.')">
                        Supprimer la Catégorie
                    </button>
                </form>
            @else
                <p class="text-red-400 text-sm mb-4">
                    Impossible de supprimer cette catégorie car elle contient {{ $category->tickets()->count() }} ticket(s).
                    Vous devez d'abord déplacer ou supprimer tous les tickets de cette catégorie.
                </p>
                <button disabled
                        class="px-4 py-2 bg-gray-600 text-gray-400 rounded-lg cursor-not-allowed">
                    Supprimer la Catégorie
                </button>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('color');
    const colorTextInput = document.getElementById('color_text');
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');

    const previewColor = document.getElementById('preview-color');
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');

    // Synchronisation des champs de couleur
    colorInput.addEventListener('input', function() {
        colorTextInput.value = this.value;
        previewColor.style.backgroundColor = this.value;
    });

    colorTextInput.addEventListener('input', function() {
        const color = this.value;
        if (/^#[0-9A-F]{6}$/i.test(color)) {
            colorInput.value = color;
            previewColor.style.backgroundColor = color;
        }
    });

    // Prévisualisation en temps réel
    nameInput.addEventListener('input', function() {
        previewName.textContent = this.value || 'Nom de la catégorie';
    });

    descriptionInput.addEventListener('input', function() {
        previewDescription.textContent = this.value || 'Aucune description';
    });
});
</script>
@endsection
