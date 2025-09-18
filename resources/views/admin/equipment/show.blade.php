{{-- resources/views/admin/equipment/show.blade.php --}}
@extends('layouts.astral-app')

@section('title', $equipment->name . ' - Détails Équipement')

@section('content')
<div class="min-h-screen p-6" style="background: linear-gradient(135deg, #0a0a0f 0%, #1a1a2e 100%);">

    <!-- Header -->
    <div class="max-w-6xl mx-auto mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.equipment.index') }}"
                   class="text-white/70 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <h1 class="text-3xl font-bold text-white">{{ $equipment->name }}</h1>
                        @if($equipment->is_featured)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.922-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Équipement Vedette
                            </span>
                        @endif
                        @if(!$equipment->is_active)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-500/20 text-gray-300 border border-gray-500/30">
                                Inactif
                            </span>
                        @endif
                    </div>
                    <p class="text-white/60">{{ $equipment->type_label }} • {{ $equipment->location ?: 'Lieu non spécifié' }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <!-- Actions rapides -->
                <div class="flex items-center space-x-2">
                    <form method="POST" action="{{ route('admin.equipment.toggle-status', $equipment) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 rounded-lg transition-colors border border-blue-500/30"
                                title="Changer le statut">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.equipment.toggle-featured', $equipment) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 bg-yellow-500/20 hover:bg-yellow-500/30 text-yellow-300 rounded-lg transition-colors border border-yellow-500/30"
                                title="Basculer vedette">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.equipment.toggle-active', $equipment) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 bg-green-500/20 hover:bg-green-500/30 text-green-300 rounded-lg transition-colors border border-green-500/30"
                                title="Basculer actif/inactif">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"/>
                            </svg>
                        </button>
                    </form>
                </div>

                <a href="{{ route('admin.equipment.edit', $equipment) }}"
                   class="px-6 py-2 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-medium rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto space-y-8">

        <!-- Informations principales -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Informations de base -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Statut et informations générales -->
                <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-6">
                    <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Informations Générales
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-white/70 text-sm font-medium mb-2">Statut</label>
                            <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium {{ $equipment->getStatusBadgeClass() }}/20 text-white border border-{{ str_replace('bg-', '', $equipment->getStatusBadgeClass()) }}/30">
                                {{ $equipment->status_label }}
                            </span>
                        </div>

                        <div>
                            <label class="block text-white/70 text-sm font-medium mb-2">Type</label>
                            <span class="text-white">{{ $equipment->type_label }}</span>
                        </div>

                        <div>
                            <label class="block text-white/70 text-sm font-medium mb-2">Localisation</label>
                            <span class="text-white">{{ $equipment->location ?: '—' }}</span>
                        </div>

                        <div>
                            <label class="block text-white/70 text-sm font-medium mb-2">Prix par heure</label>
                            <span class="text-white">
                                {{ $equipment->price_per_hour_credits ? number_format($equipment->price_per_hour_credits) . ' crédits' : 'Gratuit' }}
                            </span>
                        </div>

                        <div>
                            <label class="block text-white/70 text-sm font-medium mb-2">Créé le</label>
                            <span class="text-white">{{ $equipment->created_at->format('d/m/Y à H:i') }}</span>
                        </div>

                        <div>
                            <label class="block text-white/70 text-sm font-medium mb-2">Modifié le</label>
                            <span class="text-white">{{ $equipment->updated_at->format('d/m/Y à H:i') }}</span>
                        </div>
                    </div>

                    @if($equipment->description)
                        <div class="mt-6">
                            <label class="block text-white/70 text-sm font-medium mb-2">Description</label>
                            <div class="text-white/80 leading-relaxed">
                                {{ $equipment->description }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Spécifications techniques -->
                @if($equipment->specifications)
                <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-6">
                    <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Spécifications Techniques
                    </h3>

                    <div class="grid grid-cols-1 gap-4">
                        @foreach($equipment->specifications_formatted as $key => $value)
                            <div class="flex justify-between items-center py-3 border-b border-white/10 last:border-b-0">
                                <span class="text-white/70 font-medium capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                <span class="text-white text-right">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            <!-- Colonne droite - Médias -->
            <div class="space-y-6">

                <!-- Images -->
                @if($equipment->images && count($equipment->images) > 0)
                <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Images ({{ count($equipment->images) }})
                    </h3>

                    <div class="grid grid-cols-1 gap-4">
                        @foreach($equipment->images as $image)
                            <div class="relative group">
                                <img src="{{ Storage::url($image) }}"
                                     alt="{{ $equipment->name }}"
                                     class="w-full h-48 object-cover rounded-lg border border-white/20 group-hover:border-white/40 transition-colors cursor-pointer"
                                     onclick="showImageModal('{{ Storage::url($image) }}')">
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors rounded-lg flex items-center justify-center">
                                    <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Vidéos -->
                @if($equipment->videos && count($equipment->videos) > 0)
                <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Vidéos ({{ count($equipment->videos) }})
                    </h3>

                    <div class="space-y-4">
                        @foreach($equipment->videos as $video)
                            <div class="relative">
                                <video controls class="w-full h-48 object-cover rounded-lg border border-white/20">
                                    <source src="{{ Storage::url($video) }}" type="video/mp4">
                                    Votre navigateur ne supporte pas la lecture vidéo.
                                </video>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Actions supplémentaires -->
                <div class="bg-white/5 backdrop-blur-sm rounded-xl border border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('admin.equipment.edit', $equipment) }}"
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 rounded-lg transition-colors border border-blue-500/30">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier l'équipement
                        </a>

                        <form method="POST" action="{{ route('admin.equipment.destroy', $equipment) }}"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet équipement ? Cette action est irréversible.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-300 rounded-lg transition-colors border border-red-500/30">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Supprimer l'équipement
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal pour les images -->
<div id="imageModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4" onclick="hideImageModal()">
    <div class="max-w-4xl max-h-full">
        <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">
    </div>
</div>

@if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         class="fixed top-6 right-6 bg-green-500/20 border border-green-500/30 text-green-200 px-6 py-4 rounded-lg backdrop-blur-sm z-50">
        {{ session('success') }}
    </div>
@endif
@endsection

@push('scripts')
<script>
function showImageModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    modalImage.src = imageSrc;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function hideImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Fermer le modal avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideImageModal();
    }
});
</script>
@endpush

@push('styles')
<style>
.bg-green-500\/20 { background-color: rgb(34 197 94 / 0.2); }
.border-green-500\/30 { border-color: rgb(34 197 94 / 0.3); }
.bg-red-500\/20 { background-color: rgb(239 68 68 / 0.2); }
.border-red-500\/30 { border-color: rgb(239 68 68 / 0.3); }
.bg-orange-500\/20 { background-color: rgb(249 115 22 / 0.2); }
.border-orange-500\/30 { border-color: rgb(249 115 22 / 0.3); }
.bg-blue-500\/20 { background-color: rgb(59 130 246 / 0.2); }
.border-blue-500\/30 { border-color: rgb(59 130 246 / 0.3); }
</style>
@endpush
