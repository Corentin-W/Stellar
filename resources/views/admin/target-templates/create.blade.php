@extends('layouts.admin')

@section('title', 'Créer un Template de Target')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Créer un Template de Target
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Ajoutez une nouvelle cible au catalogue du mode assisté
                </p>
            </div>
            <a href="{{ route('admin.target-templates.index') }}"
               class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                ← Retour
            </a>
        </div>
    </div>

    <form action="{{ route('admin.target-templates.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf

        {{-- Informations de base --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                📝 Informations de base
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        ID Template *
                    </label>
                    <input type="text"
                           name="template_id"
                           value="{{ old('template_id') }}"
                           placeholder="m42, ngc7000, etc."
                           class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           required>
                    @error('template_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nom complet *
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           placeholder="M42 - Grande Nébuleuse d'Orion"
                           class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Type *
                    </label>
                    <input type="text"
                           name="type"
                           value="{{ old('type') }}"
                           placeholder="Nébuleuse, Galaxie, Amas Globulaire..."
                           class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           required>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Constellation *
                    </label>
                    <input type="text"
                           name="constellation"
                           value="{{ old('constellation') }}"
                           placeholder="Orion, Andromède..."
                           class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           required>
                    @error('constellation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Difficulté *
                    </label>
                    <select name="difficulty"
                            class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            required>
                        <option value="beginner" {{ old('difficulty') == 'beginner' ? 'selected' : '' }}>Débutant</option>
                        <option value="intermediate" {{ old('difficulty') == 'intermediate' ? 'selected' : '' }}>Intermédiaire</option>
                        <option value="advanced" {{ old('difficulty') == 'advanced' ? 'selected' : '' }}>Avancé</option>
                    </select>
                    @error('difficulty')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Temps estimé
                    </label>
                    <input type="text"
                           name="estimated_time"
                           value="{{ old('estimated_time') }}"
                           placeholder="2h20min"
                           class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('estimated_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description courte * (affichée dans le catalogue)
                </label>
                <textarea name="short_description"
                          rows="2"
                          class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                          required>{{ old('short_description') }}</textarea>
                @error('short_description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description complète
                </label>
                <textarea name="full_description"
                          rows="4"
                          class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('full_description') }}</textarea>
                @error('full_description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Conseils pour photographier
                </label>
                <textarea name="tips"
                          rows="2"
                          class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('tips') }}</textarea>
                @error('tips')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Images --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                🖼️ Images
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Image de preview (max 5MB)
                    </label>
                    <input type="file"
                           name="preview_image"
                           accept="image/*"
                           class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500">Image principale affichée en grand</p>
                    @error('preview_image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Miniature (max 2MB)
                    </label>
                    <input type="file"
                           name="thumbnail_image"
                           accept="image/*"
                           class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500">Version optimisée pour le catalogue</p>
                    @error('thumbnail_image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Coordonnées --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                🌐 Coordonnées (J2000)
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Ascension Droite (RA)</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Heures (0-23)</label>
                            <input type="number"
                                   name="ra_hours"
                                   value="{{ old('ra_hours', 0) }}"
                                   min="0" max="23"
                                   class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Minutes (0-59)</label>
                            <input type="number"
                                   name="ra_minutes"
                                   value="{{ old('ra_minutes', 0) }}"
                                   min="0" max="59"
                                   class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Secondes (0-59.9)</label>
                            <input type="number"
                                   name="ra_seconds"
                                   value="{{ old('ra_seconds', 0) }}"
                                   min="0" max="59.9" step="0.1"
                                   class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   required>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Déclinaison (DEC)</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Degrés (-90 à +90)</label>
                            <input type="number"
                                   name="dec_degrees"
                                   value="{{ old('dec_degrees', 0) }}"
                                   min="-90" max="90"
                                   class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Minutes (0-59)</label>
                            <input type="number"
                                   name="dec_minutes"
                                   value="{{ old('dec_minutes', 0) }}"
                                   min="0" max="59"
                                   class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Secondes (0-59.9)</label>
                            <input type="number"
                                   name="dec_seconds"
                                   value="{{ old('dec_seconds', 0) }}"
                                   min="0" max="59.9" step="0.1"
                                   class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Meilleurs mois --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                📅 Meilleurs mois d'observation
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @php
                $months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Jui', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
                @endphp
                @foreach($months as $month)
                <label class="flex items-center space-x-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                    <input type="checkbox"
                           name="best_months[]"
                           value="{{ $month }}"
                           {{ in_array($month, old('best_months', [])) ? 'checked' : '' }}
                           class="rounded text-blue-600">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $month }}</span>
                </label>
                @endforeach
            </div>
            @error('best_months')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Métadonnées --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                ⚙️ Paramètres
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Ordre d'affichage
                    </label>
                    <input type="number"
                           name="display_order"
                           value="{{ old('display_order', 0) }}"
                           class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500">Plus bas = affiché en premier</p>
                </div>

                <div>
                    <label class="flex items-center space-x-2 p-4 border rounded-lg cursor-pointer">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded text-blue-600">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            ✅ Template actif (visible dans le catalogue)
                        </span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.target-templates.index') }}"
               class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
                Annuler
            </a>
            <button type="submit"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                ✓ Créer le Template
            </button>
        </div>
    </form>
</div>
@endsection
