{{-- resources/views/admin/equipment/create.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Ajouter un Équipement - Admin')

@section('content')
<div class="min-h-screen p-6" style="background: linear-gradient(135deg, #0a0a0f 0%, #1a1a2e 100%);">

    <!-- Header -->
    <div class="max-w-4xl mx-auto mb-8">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.equipment.index') }}"
               class="text-white/70 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Ajouter un Équipement</h1>
                <p class="text-white/60">Créer un nouvel équipement d'observation</p>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('admin.equipment.store') }}" method="POST" enctype="multipart/form-data"
              class="space-y-8">
            @csrf

            <!-- Informations de base -->
            <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-8">
                <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informations de Base
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-white/70 text-sm font-medium mb-2">
                            Nom de l'équipement *
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-500 focus:bg-white/20 transition-colors">
                        @error('name')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="type" class="block text-white/70 text-sm font-medium mb-2">
                            Type *
                        </label>
                        <select id="type" name="type" required
                                class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                            <option value="" class="bg-gray-800">Sélectionner un type</option>
                            @foreach(\App\Models\Equipment::TYPES as $key => $label)
                                <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }} class="bg-gray-800">
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Statut -->
                    <div>
                        <label for="status" class="block text-white/70 text-sm font-medium mb-2">
                            Statut *
                        </label>
                        <select id="status" name="status" required
                                class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                            @foreach(\App\Models\Equipment::STATUSES as $key => $label)
                                <option value="{{ $key }}" {{ old('status', 'available') === $key ? 'selected' : '' }} class="bg-gray-800">
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Lieu -->
                    <div>
                        <label for="location" class="block text-white/70 text-sm font-medium mb-2">
                            Localisation
                        </label>
                        <input type="text" id="location" name="location" value="{{ old('location') }}"
                               placeholder="Ex: E-Eye, Espagne"
                               class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-500 focus:bg-white/20 transition-colors">
                        @error('location')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Prix par heure -->
                    <div>
                        <label for="price_per_hour_credits" class="block text-white/70 text-sm font-medium mb-2">
                            Prix par heure (crédits)
                        </label>
                        <input type="number" id="price_per_hour_credits" name="price_per_hour_credits" value="{{ old('price_per_hour_credits') }}"
                               min="0" placeholder="100"
                               class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-500 focus:bg-white/20 transition-colors">
                        @error('price_per_hour_credits')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-white/70 text-sm font-medium mb-2">
                            Description
                        </label>
                        <textarea id="description" name="description" rows="4"
                                  class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-500 focus:bg-white/20 transition-colors resize-vertical">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Spécifications -->
            <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-8">
                <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Spécifications Techniques
                </h3>

                <div id="specifications-container" class="space-y-4">
                    <!-- Template pour les spécifications -->
                    <div class="specification-row flex gap-4">
                        <input type="text" name="specifications[mount]" placeholder="mount"
                               class="w-1/3 bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-500">
                        <input type="text" name="specifications[mount]" placeholder="10Micron GM2000 HPS"
                               class="flex-1 bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                <button type="button" onclick="addSpecificationRow()"
                        class="mt-4 text-blue-400 hover:text-blue-300 transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Ajouter une spécification
                </button>
            </div>

            <!-- Médias -->
            <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-8">
                <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Images et Vidéos
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Images -->
                    <div>
                        <label for="images" class="block text-white/70 text-sm font-medium mb-2">
                            Images (max 5MB chacune)
                        </label>
                        <input type="file" id="images" name="images[]" multiple accept="image/*"
                               class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-500 file:text-white file:cursor-pointer hover:file:bg-blue-600">
                        @error('images.*')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Vidéos -->
                    <div>
                        <label for="videos" class="block text-white/70 text-sm font-medium mb-2">
                            Vidéos (max 50MB chacune)
                        </label>
                        <input type="file" id="videos" name="videos[]" multiple accept="video/*"
                               class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-purple-500 file:text-white file:cursor-pointer hover:file:bg-purple-600">
                        @error('videos.*')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Options -->
            <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-8">
                <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                    </svg>
                    Options
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Ordre de tri -->
                    <div>
                        <label for="sort_order" class="block text-white/70 text-sm font-medium mb-2">
                            Ordre de tri
                        </label>
                        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                               min="0" placeholder="0"
                               class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-500 focus:bg-white/20 transition-colors">
                        @error('sort_order')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Mis en avant -->
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="relative w-11 h-6 bg-white/20 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300/25 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                            <span class="ml-3 text-white/70 font-medium">Équipement vedette</span>
                        </label>
                    </div>

                    <!-- Actif -->
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="relative w-11 h-6 bg-white/20 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300/25 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                            <span class="ml-3 text-white/70 font-medium">Équipement actif</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center">
                <a href="{{ route('admin.equipment.index') }}"
                   class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-medium rounded-lg transition-colors border border-white/20">
                    Annuler
                </a>
                <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-medium rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl">
                    Créer l'équipement
                </button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
    <div class="fixed top-6 right-6 bg-red-500/20 border border-red-500/30 text-red-200 px-6 py-4 rounded-lg backdrop-blur-sm z-50">
        <div class="flex items-center space-x-2 mb-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <strong>Erreurs de validation</strong>
        </div>
        <ul class="list-disc list-inside space-y-1 text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@endsection

@push('scripts')
<script>
let specificationIndex = 1;

function addSpecificationRow() {
    const container = document.getElementById('specifications-container');
    const row = document.createElement('div');
    row.className = 'specification-row flex gap-4';
    row.innerHTML = `
        <input type="text" name="spec_keys[]" placeholder="Nom de la spécification"
               class="w-1/3 bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-500">
        <input type="text" name="spec_values[]" placeholder="Valeur"
               class="flex-1 bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-500">
        <button type="button" onclick="removeSpecificationRow(this)"
                class="px-3 py-3 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </button>
    `;
    container.appendChild(row);
}

function removeSpecificationRow(button) {
    button.closest('.specification-row').remove();
}

// Préremplit quelques spécifications communes pour les setups complets
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const container = document.getElementById('specifications-container');

    typeSelect.addEventListener('change', function() {
        if (this.value === 'complete_setup') {
            // Vider le conteneur
            container.innerHTML = '';

            // Ajouter les spécifications communes
            const commonSpecs = [
                ['mount', 'Monture'],
                ['telescope', 'Télescope'],
                ['main_camera', 'Caméra principale'],
                ['filters', 'Filtres'],
                ['guide_camera', 'Caméra de guidage'],
                ['focuser', 'Focuser'],
                ['rotator', 'Rotateur'],
                ['software', 'Logiciels']
            ];

            commonSpecs.forEach(([key, placeholder]) => {
                const row = document.createElement('div');
                row.className = 'specification-row flex gap-4';
                row.innerHTML = `
                    <input type="text" name="spec_keys[]" value="${key}" placeholder="Nom de la spécification"
                           class="w-1/3 bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-500">
                    <input type="text" name="spec_values[]" placeholder="${placeholder}"
                           class="flex-1 bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-500">
                    <button type="button" onclick="removeSpecificationRow(this)"
                            class="px-3 py-3 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                `;
                container.appendChild(row);
            });
        }
    });
});

// Validation côté client pour les fichiers
document.getElementById('images').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const maxSize = 5 * 1024 * 1024; // 5MB

    files.forEach(file => {
        if (file.size > maxSize) {
            alert(`L'image "${file.name}" est trop volumineuse (max 5MB)`);
            e.target.value = '';
        }
    });
});

document.getElementById('videos').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const maxSize = 50 * 1024 * 1024; // 50MB

    files.forEach(file => {
        if (file.size > maxSize) {
            alert(`La vidéo "${file.name}" est trop volumineuse (max 50MB)`);
            e.target.value = '';
        }
    });
});
</script>
@endpush
