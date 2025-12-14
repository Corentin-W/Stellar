@extends('layouts.astral-app')

@section('title', $target->target_name . ' - Monitoring')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="TargetMonitor('{{ $target->guid }}')" x-init="init()">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('robotarget.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                ← Retour
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ $target->target_name }}
            </h1>
            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-{{ $target->getStatusColor() }}-100 text-{{ $target->getStatusColor() }}-800">
                {{ $target->getStatusLabel() }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Target Info Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    Informations de la cible
                </h2>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">RA J2000:</span>
                        <span class="ml-2 font-mono font-semibold text-gray-900 dark:text-white">{{ $target->ra_j2000 }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">DEC J2000:</span>
                        <span class="ml-2 font-mono font-semibold text-gray-900 dark:text-white">{{ $target->dec_j2000 }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">Priorité:</span>
                        <span class="ml-2 font-semibold text-gray-900 dark:text-white">{{ $target->priority }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">Altitude min:</span>
                        <span class="ml-2 font-semibold text-gray-900 dark:text-white">{{ $target->c_alt_min }}°</span>
                    </div>
                    @if($target->c_hfd_mean_limit)
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">HFD limite:</span>
                        <span class="ml-2 font-semibold text-purple-600 dark:text-purple-400">{{ $target->c_hfd_mean_limit }} px</span>
                    </div>
                    @endif
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">Nuit noire:</span>
                        <span class="ml-2 font-semibold text-gray-900 dark:text-white">{{ $target->c_moon_down ? 'Oui 🌙' : 'Non' }}</span>
                    </div>
                </div>

                {{-- Constraint Mask --}}
                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded">
                    <span class="text-sm text-gray-600 dark:text-gray-400">C_Mask:</span>
                    <code class="ml-2 font-mono text-sm text-gray-900 dark:text-white">{{ $target->c_mask }}</code>
                </div>
            </div>

            {{-- Real-time Progress (only if executing) --}}
            <div x-show="session.status === 'running'" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    📊 Progression en temps réel
                </h2>

                {{-- Progress Bar --}}
                <div class="mb-6">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Progression</span>
                        <span class="text-sm font-semibold text-blue-600" x-text="session.progress + '%'"></span>
                    </div>
                    <div class="w-full h-4 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 transition-all duration-300" :style="'width: ' + session.progress + '%'"></div>
                    </div>
                </div>

                {{-- Current Shot Info --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Image actuelle</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white" x-text="session.currentShot + ' / ' + session.totalShots"></div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Filtre</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white" x-text="session.currentFilter || '-'"></div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">HFD actuel</div>
                        <div class="text-lg font-semibold text-purple-600" x-text="session.hfd ? session.hfd.toFixed(2) + ' px' : '-'"></div>
                    </div>
                    <div>
                        <div class="text-gray-600 dark:text-gray-400">Captures</div>
                        <div class="text-lg font-semibold text-green-600" x-text="session.shotsCaptured"></div>
                    </div>
                </div>

                {{-- WebSocket Status --}}
                <div class="mt-4 flex items-center gap-2">
                    <div class="flex items-center gap-2" x-show="isConnected">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400">Connecté en temps réel</span>
                    </div>
                    <div class="flex items-center gap-2" x-show="!isConnected">
                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400">Déconnecté</span>
                    </div>
                </div>
            </div>

            {{-- Shots Configuration --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    📸 Configuration d'acquisition
                </h2>

                <div class="space-y-3">
                    @foreach($target->shots as $shot)
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $shot->filter_name }}</span>
                                    <span class="text-gray-600 dark:text-gray-400 ml-2">
                                        {{ $shot->num }} × {{ $shot->exposure }}s
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    Total: {{ $shot->getFormattedExposureTime() }}
                                </div>
                            </div>
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Gain: {{ $shot->gain }} | Offset: {{ $shot->offset }} | Bin: {{ $shot->bin }}x{{ $shot->bin }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded">
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <strong>Durée totale estimée:</strong> {{ $target->getFormattedDuration() }}
                    </div>
                </div>
            </div>

            {{-- Sessions History --}}
            @if($target->sessions->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    📜 Historique des sessions
                </h2>

                <div class="space-y-3">
                    @foreach($target->sessions as $sessionItem)
                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    Session {{ $sessionItem->id }}
                                </span>
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-{{ $sessionItem->getResultColor() }}-100 text-{{ $sessionItem->getResultColor() }}-800">
                                    {{ $sessionItem->getResultLabel() }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Début:</span>
                                    <span class="ml-1 text-gray-900 dark:text-white">{{ $sessionItem->session_start->format('d/m/Y H:i') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Fin:</span>
                                    <span class="ml-1 text-gray-900 dark:text-white">{{ $sessionItem->session_end?->format('d/m/Y H:i') ?? '-' }}</span>
                                </div>
                                @if($sessionItem->hfd_mean)
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">HFD moyen:</span>
                                    <span class="ml-1 text-purple-600 font-semibold">{{ $sessionItem->hfd_mean }} px</span>
                                </div>
                                @endif
                                @if($sessionItem->images_captured)
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400">Images:</span>
                                    <span class="ml-1 text-green-600 font-semibold">{{ $sessionItem->images_captured }} / {{ $sessionItem->images_accepted ?? 0 }} acceptées</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Credits Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    💰 Crédits
                </h3>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Estimés:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $target->estimated_credits }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Bloqués:</span>
                        <span class="font-semibold text-orange-600">{{ $target->credits_held }}</span>
                    </div>
                    @if($target->credits_charged > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Débités:</span>
                        <span class="font-semibold text-blue-600">{{ $target->credits_charged }}</span>
                    </div>
                    @endif
                </div>

                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded text-xs text-gray-600 dark:text-gray-400">
                    Les crédits seront débités uniquement si la session réussit. En cas d'erreur, ils seront automatiquement remboursés.
                </div>
            </div>

            {{-- Actions Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    ⚡ Actions
                </h3>

                @if($target->isPending())
                    <button @click="submitTarget()"
                            :disabled="isLoading"
                            class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-3 rounded-lg font-medium transition-colors mb-3">
                        <span x-show="!isLoading">▶️ Soumettre à Voyager</span>
                        <span x-show="isLoading">⏳ Envoi en cours...</span>
                    </button>
                @elseif($target->isActive() || $target->isExecuting())
                    <button @click="cancelTarget()"
                            :disabled="isLoading"
                            class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white px-4 py-3 rounded-lg font-medium transition-colors mb-3">
                        <span x-show="!isLoading">❌ Annuler</span>
                        <span x-show="isLoading">⏳ Annulation...</span>
                    </button>
                @endif

                <a href="{{ route('robotarget.index') }}"
                   class="block w-full text-center bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded-lg font-medium transition-colors">
                    ← Retour à la liste
                </a>
            </div>

            {{-- Info Card --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 text-sm">
                <div class="font-semibold text-blue-800 dark:text-blue-200 mb-2">
                    ℹ️ Informations
                </div>
                <div class="text-blue-700 dark:text-blue-300 space-y-1">
                    <p>• GUID: <code class="text-xs">{{ $target->guid }}</code></p>
                    <p>• Créée le {{ $target->created_at->format('d/m/Y à H:i') }}</p>
                    @if($target->updated_at != $target->created_at)
                    <p>• Dernière MAJ: {{ $target->updated_at->diffForHumans() }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- TargetMonitor component is loaded globally via app.js --}}
