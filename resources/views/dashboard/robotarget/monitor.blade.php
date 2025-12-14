@extends('layouts.astral-app')

@section('title', 'Monitoring Live - ' . $target->target_name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 p-4"
     x-data="LiveMonitor({{ $session->id }}, {{ auth()->id() }})"
     x-init="init()">

    <!-- Header -->
    <div class="max-w-7xl mx-auto mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">
                    🔭 {{ $target->target_name }}
                </h1>
                <p class="text-purple-300">Monitoring en temps réel</p>
            </div>
            <div class="flex items-center gap-4">
                <!-- Connection Status -->
                <div class="flex items-center gap-2 px-4 py-2 bg-black/30 rounded-lg backdrop-blur">
                    <div class="relative">
                        <div class="w-3 h-3 rounded-full"
                             :class="connected ? 'bg-green-500 animate-pulse' : 'bg-red-500'"></div>
                        <div class="absolute inset-0 w-3 h-3 rounded-full"
                             :class="connected ? 'bg-green-500' : 'bg-red-500'"
                             style="animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;"></div>
                    </div>
                    <span class="text-white text-sm" x-text="connected ? 'Connecté' : 'Déconnecté'"></span>
                </div>

                <!-- Back Button -->
                <a href="{{ route('robotarget.show', ['locale' => app()->getLocale(), 'guid' => $target->guid]) }}"
                   class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                    ← Retour
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Progress & Stats -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Progress Card -->
            <div class="bg-black/20 backdrop-blur rounded-xl p-6 border border-purple-500/30">
                <h2 class="text-xl font-bold text-white mb-4">Progression</h2>

                <!-- Progress Bar -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-white text-sm">Avancement global</span>
                        <span class="text-purple-300 font-bold" x-text="progress.percentage + '%'"></span>
                    </div>
                    <div class="w-full h-3 bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full transition-all duration-500 rounded-full"
                             :class="getProgressBarColor()"
                             :style="`width: ${progress.percentage}%`"></div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-purple-500/10 rounded-lg p-4">
                        <div class="text-purple-300 text-xs mb-1">Images Prises</div>
                        <div class="text-white text-2xl font-bold" x-text="progress.currentShot || 0"></div>
                    </div>
                    <div class="bg-blue-500/10 rounded-lg p-4">
                        <div class="text-blue-300 text-xs mb-1">Total Prévu</div>
                        <div class="text-white text-2xl font-bold" x-text="progress.totalShots || 0"></div>
                    </div>
                    <div class="bg-yellow-500/10 rounded-lg p-4">
                        <div class="text-yellow-300 text-xs mb-1">Temps Restant</div>
                        <div class="text-white text-2xl font-bold" x-text="formatDuration(progress.remaining)"></div>
                    </div>
                    <div class="bg-green-500/10 rounded-lg p-4">
                        <div class="text-green-300 text-xs mb-1">HFD Moyen</div>
                        <div class="text-white text-2xl font-bold" x-text="formatHFD(camera.hfd)"></div>
                    </div>
                </div>
            </div>

            <!-- Current Image -->
            <div class="bg-black/20 backdrop-blur rounded-xl p-6 border border-purple-500/30">
                <h2 class="text-xl font-bold text-white mb-4">📸 Dernière Image</h2>

                <div class="relative aspect-video bg-gray-900 rounded-lg overflow-hidden">
                    <template x-if="currentImage && currentImage.thumbnail">
                        <img :src="`data:image/jpeg;base64,${currentImage.thumbnail}`"
                             :alt="`Image ${currentImage.filename}`"
                             class="w-full h-full object-contain">
                    </template>
                    <template x-if="!currentImage">
                        <div class="absolute inset-0 flex items-center justify-center text-gray-500">
                            <div class="text-center">
                                <div class="text-6xl mb-4">📷</div>
                                <div>En attente d'images...</div>
                            </div>
                        </div>
                    </template>
                </div>

                <template x-if="currentImage">
                    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div>
                            <div class="text-gray-400">Filtre</div>
                            <div class="text-white font-semibold" x-text="currentImage.filter || 'N/A'"></div>
                        </div>
                        <div>
                            <div class="text-gray-400">Exposition</div>
                            <div class="text-white font-semibold" x-text="(currentImage.exposure || 0) + 's'"></div>
                        </div>
                        <div>
                            <div class="text-gray-400">HFD</div>
                            <div class="text-white font-semibold" x-text="formatHFD(currentImage.hfd)"></div>
                        </div>
                        <div>
                            <div class="text-gray-400">Heure</div>
                            <div class="text-white font-semibold" x-text="currentImage.timestamp ? new Date(currentImage.timestamp).toLocaleTimeString() : 'N/A'"></div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Images Gallery -->
            <div class="bg-black/20 backdrop-blur rounded-xl p-6 border border-purple-500/30">
                <h2 class="text-xl font-bold text-white mb-4">🖼️ Galerie (Dernières Images)</h2>

                <div class="grid grid-cols-4 md:grid-cols-6 gap-2">
                    <template x-for="image in images" :key="image.id">
                        <div class="aspect-square bg-gray-800 rounded-lg overflow-hidden cursor-pointer hover:ring-2 hover:ring-purple-500 transition-all"
                             @click="currentImage = image">
                            <img :src="`data:image/jpeg;base64,${image.thumbnail}`"
                                 :alt="`Image ${image.filename}`"
                                 class="w-full h-full object-cover">
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Right Column - Telemetry & Notifications -->
        <div class="space-y-6">
            <!-- Camera & Mount Status -->
            <div class="bg-black/20 backdrop-blur rounded-xl p-6 border border-purple-500/30">
                <h2 class="text-lg font-bold text-white mb-4">📡 Télémétrie</h2>

                <!-- Camera -->
                <div class="mb-6">
                    <div class="text-purple-300 text-sm mb-2">Caméra</div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">Température</span>
                            <span class="text-white font-semibold" x-text="formatTemperature(camera.temperature)"></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">Cooling</span>
                            <span :class="camera.cooling ? 'text-green-400' : 'text-gray-400'" x-text="camera.cooling ? 'ON' : 'OFF'"></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">HFD</span>
                            <span class="text-white font-semibold" x-text="formatHFD(camera.hfd)"></span>
                        </div>
                    </div>
                </div>

                <!-- Mount -->
                <div>
                    <div class="text-purple-300 text-sm mb-2">Monture</div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">Tracking</span>
                            <span :class="mount.tracking ? 'text-green-400' : 'text-red-400'" x-text="mount.tracking ? 'ON' : 'OFF'"></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">RA</span>
                            <span class="text-white font-semibold font-mono text-xs" x-text="mount.ra || 'N/A'"></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400">DEC</span>
                            <span class="text-white font-semibold font-mono text-xs" x-text="mount.dec || 'N/A'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Info -->
            <div class="bg-black/20 backdrop-blur rounded-xl p-6 border border-purple-500/30">
                <h2 class="text-lg font-bold text-white mb-4">ℹ️ Info Session</h2>
                <div class="space-y-3 text-sm">
                    <div>
                        <div class="text-gray-400">Cible</div>
                        <div class="text-white font-semibold">{{ $target->target_name }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400">Coordonnées</div>
                        <div class="text-white font-mono text-xs">{{ $target->ra_j2000 }} / {{ $target->dec_j2000 }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400">Démarrage</div>
                        <div class="text-white">{{ $session->session_start?->format('d/m/Y H:i') ?? 'En attente' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400">Session GUID</div>
                        <div class="text-purple-300 font-mono text-xs break-all">{{ $session->session_guid }}</div>
                    </div>
                </div>
            </div>

            <!-- Notifications Feed -->
            <div class="bg-black/20 backdrop-blur rounded-xl p-6 border border-purple-500/30">
                <h2 class="text-lg font-bold text-white mb-4">🔔 Notifications</h2>

                <div class="space-y-2 max-h-64 overflow-y-auto">
                    <template x-for="notif in notifications" :key="notif.id">
                        <div class="p-3 rounded-lg text-sm"
                             :class="{
                                 'bg-green-500/20 text-green-300': notif.type === 'success',
                                 'bg-blue-500/20 text-blue-300': notif.type === 'info',
                                 'bg-red-500/20 text-red-300': notif.type === 'error'
                             }">
                            <div x-text="notif.message"></div>
                            <div class="text-xs opacity-70 mt-1" x-text="notif.timestamp.toLocaleTimeString()"></div>
                        </div>
                    </template>

                    <template x-if="notifications.length === 0">
                        <div class="text-gray-500 text-sm text-center py-8">
                            Aucune notification
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes ping {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}
</style>
@endsection
