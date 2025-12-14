@extends('layouts.app-astral')

@section('title', 'Galerie d\'Images')

@section('content')
<div x-data="galleryManager()" x-init="init()">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">
                🖼️ Galerie d'Images
            </h1>
            <p class="text-gray-400">
                Toutes vos images capturées depuis le télescope
            </p>
        </div>

        <div class="flex gap-3">
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg px-6 py-4">
                <div class="text-gray-400 text-sm mb-1">Total Sessions</div>
                <div class="text-2xl font-bold text-white" x-text="gallery.length">0</div>
            </div>
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg px-6 py-4">
                <div class="text-gray-400 text-sm mb-1">Total Images</div>
                <div class="text-2xl font-bold text-white" x-text="totalImages">0</div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="isLoading" class="text-center py-20">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-purple-500"></div>
        <p class="text-gray-400 mt-4">Chargement de votre galerie...</p>
    </div>

    <!-- Error State -->
    <div x-show="errorMessage" class="bg-red-500/10 border border-red-500/50 rounded-lg p-4 mb-6">
        <p class="text-red-400" x-text="errorMessage"></p>
    </div>

    <!-- Empty State -->
    <div x-show="!isLoading && gallery.length === 0" class="text-center py-20">
        <div class="text-6xl mb-4">📸</div>
        <h3 class="text-xl font-bold text-white mb-2">Aucune image pour le moment</h3>
        <p class="text-gray-400 mb-6">
            Créez votre première target pour commencer à capturer des images du ciel profond.
        </p>
        <a href="{{ route('robotarget.create', ['locale' => app()->getLocale()]) }}"
           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold rounded-lg hover:opacity-90 transition">
            ✨ Créer une Target
        </a>
    </div>

    <!-- Gallery Grid -->
    <div x-show="!isLoading && gallery.length > 0" class="space-y-8">
        <template x-for="session in gallery" :key="session.session_id">
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg overflow-hidden">
                <!-- Session Header -->
                <div class="border-b border-white/10 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-1" x-text="session.target_name"></h3>
                            <p class="text-gray-400 text-sm">
                                Session du <span x-text="formatDate(session.session_started_at)"></span>
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <div class="text-sm text-gray-400">Durée</div>
                                <div class="text-white font-medium" x-text="formatDuration(session.total_duration)"></div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-400">Images</div>
                                <div class="text-white font-medium" x-text="session.images_count"></div>
                            </div>
                            <a :href="`/dashboard/robotarget/${session.target_id}`"
                               class="px-4 py-2 bg-purple-500/20 text-purple-300 rounded-lg hover:bg-purple-500/30 transition text-sm">
                                Voir Target
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Images Grid -->
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        <template x-for="shot in session.shots" :key="shot.guid">
                            <div @click="openImageModal(shot, session)"
                                 class="group relative aspect-square bg-black/50 rounded-lg overflow-hidden cursor-pointer border border-white/10 hover:border-purple-500/50 transition">

                                <!-- Thumbnail (we'll load via API) -->
                                <img :src="`/api/robotarget/shots/${shot.guid}/jpg`"
                                     :alt="shot.filename"
                                     class="w-full h-full object-cover transition group-hover:scale-105"
                                     loading="lazy">

                                <!-- Overlay on hover -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition">
                                    <div class="absolute bottom-0 left-0 right-0 p-3">
                                        <div class="text-white text-xs font-medium mb-1" x-text="shot.filename.substring(0, 25) + '...'"></div>
                                        <div class="flex gap-2 text-xs text-gray-300">
                                            <span>⭐ HFD: <span x-text="shot.hfd?.toFixed(2)"></span></span>
                                            <span>✨ SI: <span x-text="shot.starindex?.toFixed(2)"></span></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Filter badge -->
                                <div class="absolute top-2 right-2 px-2 py-1 bg-black/70 text-white text-xs rounded">
                                    <span x-text="getFilterName(shot.filterindex)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Image Modal -->
    <div x-show="selectedShot"
         x-cloak
         @click.self="closeImageModal()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm p-4">

        <div class="relative max-w-7xl w-full bg-gray-900 rounded-lg overflow-hidden shadow-2xl">
            <!-- Close button -->
            <button @click="closeImageModal()"
                    class="absolute top-4 right-4 z-10 p-2 bg-black/50 hover:bg-black/70 text-white rounded-full transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="grid md:grid-cols-3">
                <!-- Image -->
                <div class="md:col-span-2 bg-black p-4">
                    <img :src="`/api/robotarget/shots/${selectedShot?.guid}/jpg`"
                         :alt="selectedShot?.filename"
                         class="w-full h-auto max-h-[80vh] object-contain">
                </div>

                <!-- Metadata -->
                <div class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
                    <div>
                        <h3 class="text-xl font-bold text-white mb-4">Métadonnées</h3>

                        <div class="space-y-4">
                            <div>
                                <div class="text-sm text-gray-400">Fichier</div>
                                <div class="text-white font-mono text-xs break-all" x-text="selectedShot?.filename"></div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-400">Filtre</div>
                                    <div class="text-white" x-text="getFilterName(selectedShot?.filterindex)"></div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-400">Binning</div>
                                    <div class="text-white" x-text="selectedShot?.bin + 'x' + selectedShot?.bin"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-400">Exposition</div>
                                    <div class="text-white" x-text="selectedShot?.exposure + 's'"></div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-400">Date</div>
                                    <div class="text-white text-xs" x-text="formatDate(selectedShot?.datetimeshotutc)"></div>
                                </div>
                            </div>

                            <div class="border-t border-white/10 pt-4">
                                <div class="text-sm text-gray-400 mb-3">Qualité</div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-300">HFD</span>
                                        <span class="text-white font-medium" x-text="selectedShot?.hfd?.toFixed(2)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-300">Star Index</span>
                                        <span class="text-white font-medium" x-text="selectedShot?.starindex?.toFixed(2)"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-white/10 pt-4">
                                <div class="text-sm text-gray-400 mb-3">Histogramme</div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-300">Min ADU</span>
                                        <span class="text-white font-medium" x-text="selectedShot?.min"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-300">Max ADU</span>
                                        <span class="text-white font-medium" x-text="selectedShot?.max"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-300">Mean ADU</span>
                                        <span class="text-white font-medium" x-text="selectedShot?.mean"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-white/10 pt-4">
                                <a :href="`/api/robotarget/shots/${selectedShot?.guid}/jpg`"
                                   download
                                   class="w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold rounded-lg hover:opacity-90 transition">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Télécharger JPG
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function galleryManager() {
    return {
        gallery: [],
        isLoading: true,
        errorMessage: null,
        selectedShot: null,
        selectedSession: null,

        async init() {
            await this.loadGallery();
        },

        async loadGallery() {
            this.isLoading = true;
            this.errorMessage = null;

            try {
                const response = await fetch('/api/robotarget/gallery', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                const data = await response.json();

                if (data.success) {
                    this.gallery = data.gallery || [];
                } else {
                    this.errorMessage = data.message || 'Erreur lors du chargement de la galerie';
                }
            } catch (error) {
                this.errorMessage = 'Erreur réseau lors du chargement de la galerie';
                console.error('Gallery load error:', error);
            } finally {
                this.isLoading = false;
            }
        },

        get totalImages() {
            return this.gallery.reduce((total, session) => total + session.images_count, 0);
        },

        openImageModal(shot, session) {
            this.selectedShot = shot;
            this.selectedSession = session;
        },

        closeImageModal() {
            this.selectedShot = null;
            this.selectedSession = null;
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        formatDuration(seconds) {
            if (!seconds) return 'N/A';
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            return `${hours}h ${minutes}min`;
        },

        getFilterName(filterIndex) {
            const filters = {
                0: 'L (Luminance)',
                1: 'R (Red)',
                2: 'G (Green)',
                3: 'B (Blue)',
                4: 'Ha (H-alpha)',
                5: 'OIII (Oxygen-III)',
                6: 'SII (Sulfur-II)',
            };
            return filters[filterIndex] || `Filter ${filterIndex}`;
        },
    };
}
</script>
@endpush

@push('styles')
<style>
[x-cloak] {
    display: none !important;
}
</style>
@endpush
