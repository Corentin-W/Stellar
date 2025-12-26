{{-- resources/views/admin/robotarget/sets.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Voyager Control Panel')

@section('content')
<div x-data="voyagerControl()" x-init="init()">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-900 to-purple-900 rounded-xl p-6 shadow-2xl border border-white/10 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">🔭 Voyager Control Panel</h1>
                <p class="text-gray-300 mt-1">Contrôle complet de Voyager - Sets, Targets & Shots</p>
            </div>
            <div class="flex items-center gap-4">
                <!-- Connection Status -->
                <div class="bg-gray-800 rounded-lg px-4 py-2">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full" :class="connected ? 'bg-green-500 animate-pulse' : 'bg-red-500'"></div>
                        <span class="text-sm text-white" x-text="connected ? 'Connecté' : 'Déconnecté'"></span>
                    </div>
                </div>
                <!-- Hardware Config Button -->
                <button @click="viewHardwareConfig()"
                        class="bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg font-medium text-white">
                    ⚙️ Configuration
                </button>
                <!-- Refresh Button -->
                <button @click="refreshSets()"
                        :disabled="loading"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-600 px-4 py-2 rounded-lg font-medium text-white">
                    <span x-show="!loading">🔄 Rafraîchir</span>
                    <span x-show="loading">⏳ Chargement...</span>
                </button>
                <!-- Add Set Button -->
                <button @click="openCreateModal()"
                        class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg font-medium text-white">
                    ➕ Nouveau Set
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white/5 rounded-lg p-4 border border-white/10">
            <div class="text-gray-400 text-sm">Total Sets</div>
            <div class="text-2xl font-bold text-white" x-text="sets.length"></div>
        </div>
        <div class="bg-white/5 rounded-lg p-4 border border-white/10">
            <div class="text-gray-400 text-sm">Actifs</div>
            <div class="text-2xl font-bold text-green-400" x-text="sets.filter(s => s.status === 0).length"></div>
        </div>
        <div class="bg-white/5 rounded-lg p-4 border border-white/10">
            <div class="text-gray-400 text-sm">Inactifs</div>
            <div class="text-2xl font-bold text-red-400" x-text="sets.filter(s => s.status === 1).length"></div>
        </div>
        <div class="bg-white/5 rounded-lg p-4 border border-white/10">
            <div class="text-gray-400 text-sm">Profils</div>
            <div class="text-2xl font-bold text-blue-400" x-text="[...new Set(sets.map(s => s.profilename))].length"></div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white/5 rounded-lg p-4 mb-6 border border-white/10">
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <input type="text"
                       x-model="searchQuery"
                       placeholder="🔍 Rechercher par nom, tag, ou profil..."
                       class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white placeholder-gray-400">
            </div>
            <select x-model="filterStatus" class="bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white">
                <option value="all">Tous les statuts</option>
                <option value="0">Actifs uniquement</option>
                <option value="1">Inactifs uniquement</option>
            </select>
            <select x-model="filterProfile" class="bg-white/10 border border-white/20 rounded-lg px-4 py-2 text-white">
                <option value="">Tous les profils</option>
                <template x-for="profile in [...new Set(sets.map(s => s.profilename))]" :key="profile">
                    <option :value="profile" x-text="profile"></option>
                </template>
            </select>
        </div>
    </div>

    <!-- Sets Table -->
    <div class="bg-white/5 rounded-lg border border-white/10 overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/10 border-b border-white/10">
                <tr>
                    <th class="px-4 py-3 text-left text-white">Nom</th>
                    <th class="px-4 py-3 text-left text-white">Profil</th>
                    <th class="px-4 py-3 text-left text-white">Tag</th>
                    <th class="px-4 py-3 text-left text-white">Statut</th>
                    <th class="px-4 py-3 text-left text-white">Défaut</th>
                    <th class="px-4 py-3 text-right text-white">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="set in filteredSets" :key="set.guid">
                    <tr class="border-b border-white/10 hover:bg-white/5 transition">
                        <td class="px-4 py-3">
                            <div class="font-medium text-white" x-text="set.setname"></div>
                            <div class="text-xs text-gray-400" x-text="set.guid"></div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-300" x-text="set.profilename"></div>
                        </td>
                        <td class="px-4 py-3">
                            <span x-show="set.tag"
                                  class="bg-blue-600 px-2 py-1 rounded text-xs text-white"
                                  x-text="set.tag"></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs font-medium text-white"
                                  :class="set.status === 0 ? 'bg-green-600' : 'bg-red-600'"
                                  x-text="set.status === 0 ? 'Actif' : 'Inactif'"></span>
                        </td>
                        <td class="px-4 py-3">
                            <span x-show="set.isdefault" class="text-yellow-400">⭐</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="viewTargets(set)"
                                        class="bg-purple-600 hover:bg-purple-700 px-3 py-1 rounded text-sm text-white">
                                    🎯 Targets
                                </button>
                                <button @click="viewSet(set)"
                                        class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-sm text-white">
                                    👁️ Voir
                                </button>
                                <button @click="editSet(set)"
                                        class="bg-yellow-600 hover:bg-yellow-700 px-3 py-1 rounded text-sm text-white">
                                    ✏️ Modifier
                                </button>
                                <button @click="toggleSet(set)"
                                        :class="set.status === 0 ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700'"
                                        class="px-3 py-1 rounded text-sm text-white">
                                    <span x-text="set.status === 0 ? '🔒 Désactiver' : '🔓 Activer'"></span>
                                </button>
                                <button @click="deleteSet(set)"
                                        class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm text-white">
                                    🗑️ Supprimer
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="filteredSets.length === 0">
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                        Aucun Set trouvé
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Modal Création/Edition -->
    <div x-show="showModal"
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
         @click.self="closeModal()">
        <div class="bg-gray-800 rounded-lg max-w-2xl w-full mx-4 border border-gray-700">
            <div class="bg-gradient-to-r from-blue-900 to-purple-900 px-6 py-4 rounded-t-lg border-b border-gray-700">
                <h2 class="text-xl font-bold text-white" x-text="modalMode === 'create' ? '➕ Créer un nouveau Set' : '✏️ Modifier le Set'"></h2>
            </div>
            <div class="p-6">
                <form @submit.prevent="saveSet()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Nom du Set *</label>
                        <input type="text"
                               x-model="formData.name"
                               required
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Profil Voyager *</label>
                        <select x-model="formData.profile_name"
                                required
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white">
                            <option value="">Sélectionnez un profil</option>
                            <template x-for="profile in [...new Set(sets.map(s => s.profilename))]" :key="profile">
                                <option :value="profile" x-text="profile"></option>
                            </template>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-white">Tag</label>
                            <input type="text"
                                   x-model="formData.tag"
                                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2 text-white">Statut</label>
                            <select x-model="formData.status"
                                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white">
                                <option :value="0">Actif</option>
                                <option :value="1">Inactif</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-white">Note</label>
                        <textarea x-model="formData.note"
                                  rows="3"
                                  class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-700">
                        <button type="button"
                                @click="closeModal()"
                                class="bg-gray-600 hover:bg-gray-700 px-6 py-2 rounded-lg text-white">
                            Annuler
                        </button>
                        <button type="submit"
                                :disabled="saving"
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-600 px-6 py-2 rounded-lg font-medium text-white">
                            <span x-show="!saving" x-text="modalMode === 'create' ? '➕ Créer' : '💾 Enregistrer'"></span>
                            <span x-show="saving">⏳ Enregistrement...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Détails -->
    <div x-show="showDetailsModal"
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
         @click.self="closeDetailsModal()">
        <div class="bg-gray-800 rounded-lg max-w-3xl w-full mx-4 border border-gray-700">
            <div class="bg-gradient-to-r from-blue-900 to-purple-900 px-6 py-4 rounded-t-lg border-b border-gray-700">
                <h2 class="text-xl font-bold text-white">👁️ Détails du Set</h2>
            </div>
            <div class="p-6" x-show="selectedSet">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-400">Nom</div>
                            <div class="font-medium text-lg text-white" x-text="selectedSet?.setname"></div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-400">GUID</div>
                            <div class="font-mono text-sm text-white" x-text="selectedSet?.guid"></div>
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-400">Profil Voyager</div>
                        <div class="font-medium text-white" x-text="selectedSet?.profilename"></div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <div class="text-sm text-gray-400">Statut</div>
                            <span class="inline-block px-3 py-1 rounded text-sm font-medium mt-1 text-white"
                                  :class="selectedSet?.status === 0 ? 'bg-green-600' : 'bg-red-600'"
                                  x-text="selectedSet?.status === 0 ? 'Actif' : 'Inactif'"></span>
                        </div>
                        <div>
                            <div class="text-sm text-gray-400">Set par défaut</div>
                            <div class="mt-1 text-white" x-text="selectedSet?.isdefault ? '⭐ Oui' : 'Non'"></div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-400">Tag</div>
                            <div class="mt-1">
                                <span x-show="selectedSet?.tag"
                                      class="bg-blue-600 px-2 py-1 rounded text-sm text-white"
                                      x-text="selectedSet?.tag"></span>
                                <span x-show="!selectedSet?.tag" class="text-gray-500">Aucun</span>
                            </div>
                        </div>
                    </div>

                    <div x-show="selectedSet?.note">
                        <div class="text-sm text-gray-400">Note</div>
                        <div class="bg-gray-700 rounded-lg p-3 mt-1 text-white" x-text="selectedSet?.note"></div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-700 mt-6">
                    <button @click="closeDetailsModal()"
                            class="bg-gray-600 hover:bg-gray-700 px-6 py-2 rounded-lg text-white">
                        Fermer
                    </button>
                    <button @click="editSet(selectedSet); closeDetailsModal();"
                            class="bg-blue-600 hover:bg-blue-700 px-6 py-2 rounded-lg text-white">
                        ✏️ Modifier
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Targets -->
    <div x-show="showTargetsModal"
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
         @click.self="closeTargetsModal()">
        <div class="bg-gray-800 rounded-lg max-w-5xl w-full mx-4 border border-gray-700 max-h-[90vh] overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-purple-900 to-blue-900 px-6 py-4 rounded-t-lg border-b border-gray-700">
                <h2 class="text-xl font-bold text-white">🎯 Targets du Set : <span x-text="selectedSet?.setname"></span></h2>
            </div>
            <div class="p-6 overflow-y-auto flex-1">
                <div x-show="loadingTargets" class="text-center py-8">
                    <div class="text-white">⏳ Chargement des Targets...</div>
                </div>
                <div x-show="!loadingTargets && currentSetTargets.length === 0" class="text-center py-8">
                    <div class="text-gray-400">Aucune Target dans ce Set</div>
                </div>
                <div x-show="!loadingTargets && currentSetTargets.length > 0" class="space-y-4">
                    <template x-for="target in currentSetTargets" :key="target.guid">
                        <div class="bg-white/5 border border-white/10 rounded-lg p-4 hover:bg-white/10 transition">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="font-medium text-white text-lg" x-text="target.targetname"></div>
                                    <div class="text-sm text-gray-400 mt-1">
                                        <span>RA: <span x-text="target.ra"></span></span> |
                                        <span>DEC: <span x-text="target.dec"></span></span>
                                    </div>
                                    <div class="flex gap-2 mt-2">
                                        <span class="px-2 py-1 rounded text-xs bg-blue-600 text-white" x-show="target.c_moondown">🌙 Moon Down</span>
                                        <span class="px-2 py-1 rounded text-xs bg-green-600 text-white" x-show="target.targetactive">Actif</span>
                                        <span class="px-2 py-1 rounded text-xs bg-red-600 text-white" x-show="!target.targetactive">Inactif</span>
                                    </div>
                                </div>
                                <button @click="viewShots(target)"
                                        class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded text-white">
                                    📸 Voir Shots
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-700">
                <button @click="closeTargetsModal()"
                        class="bg-gray-600 hover:bg-gray-700 px-6 py-2 rounded-lg text-white">
                    Fermer
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Shots -->
    <div x-show="showShotsModal"
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
         @click.self="closeShotsModal()">
        <div class="bg-gray-800 rounded-lg max-w-6xl w-full mx-4 border border-gray-700 max-h-[90vh] overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-indigo-900 to-purple-900 px-6 py-4 rounded-t-lg border-b border-gray-700">
                <h2 class="text-xl font-bold text-white">📸 Plan d'acquisition : <span x-text="selectedTarget?.targetname"></span></h2>
            </div>
            <div class="p-6 overflow-y-auto flex-1">
                <div x-show="loadingShots" class="text-center py-8">
                    <div class="text-white">⏳ Chargement des Shots...</div>
                </div>
                <div x-show="!loadingShots && currentTargetShots.length === 0" class="text-center py-8">
                    <div class="text-gray-400">Aucun Shot configuré pour cette Target</div>
                </div>
                <div x-show="!loadingShots && currentTargetShots.length > 0">
                    <table class="w-full">
                        <thead class="bg-white/10 border-b border-white/10">
                            <tr>
                                <th class="px-4 py-3 text-left text-white">Filtre</th>
                                <th class="px-4 py-3 text-left text-white">Exposition</th>
                                <th class="px-4 py-3 text-left text-white">Quantité</th>
                                <th class="px-4 py-3 text-left text-white">Binning</th>
                                <th class="px-4 py-3 text-left text-white">Gain</th>
                                <th class="px-4 py-3 text-left text-white">Progression</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="shot in currentTargetShots" :key="shot.guid">
                                <tr class="border-b border-white/10 hover:bg-white/5 transition">
                                    <td class="px-4 py-3">
                                        <span class="px-3 py-1 rounded bg-indigo-600 text-white font-medium"
                                              x-text="getFilterName(shot.filterindex)"></span>
                                    </td>
                                    <td class="px-4 py-3 text-white" x-text="formatExposure(shot.exposure)"></td>
                                    <td class="px-4 py-3 text-white" x-text="shot.num + 'x'"></td>
                                    <td class="px-4 py-3 text-white" x-text="shot.bin + 'x' + shot.bin"></td>
                                    <td class="px-4 py-3 text-gray-300" x-text="shot.gain || '-'"></td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-700 rounded-full h-2 overflow-hidden">
                                                <div class="bg-green-500 h-full transition-all duration-300"
                                                     :style="`width: ${shot.auxtotshot > 0 ? (shot.auxshotdone / shot.auxtotshot * 100) : 0}%`"></div>
                                            </div>
                                            <span class="text-sm text-white whitespace-nowrap"
                                                  x-text="`${shot.auxshotdone || 0}/${shot.auxtotshot || shot.num}`"></span>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div class="mt-4 bg-blue-900/20 border border-blue-500/30 rounded-lg p-4">
                        <div class="text-sm text-blue-300">
                            💡 <strong>Info:</strong> Le plan d'acquisition montre les poses programmées. La progression indique combien d'images ont été acceptées.
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-700">
                <button @click="closeShotsModal()"
                        class="bg-gray-600 hover:bg-gray-700 px-6 py-2 rounded-lg text-white">
                    Fermer
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Configuration Matérielle -->
    <div x-show="showHardwareConfigModal"
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
         @click.self="closeHardwareConfigModal()">
        <div class="bg-gray-800 rounded-lg max-w-5xl w-full mx-4 border border-gray-700 max-h-[90vh] overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-indigo-900 to-blue-900 px-6 py-4 rounded-t-lg border-b border-gray-700">
                <h2 class="text-xl font-bold text-white">⚙️ Configuration Matérielle Voyager</h2>
            </div>
            <div class="p-6 overflow-y-auto flex-1">
                <div x-show="loadingHardwareConfig" class="text-center py-8">
                    <div class="text-white">⏳ Chargement de la configuration...</div>
                </div>

                <div x-show="!loadingHardwareConfig && hardwareConfig">
                    <!-- Profil Actif -->
                    <div class="bg-white/5 rounded-lg p-4 mb-4 border border-white/10">
                        <h3 class="text-lg font-bold text-white mb-3">📋 Profil Actif</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <div class="text-sm text-gray-400">Nom du profil</div>
                                <div class="text-white font-medium" x-text="hardwareConfig?.activeProfile?.name || 'N/A'"></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-400">Type de capteur</div>
                                <div class="text-white font-medium" x-text="hardwareConfig?.activeProfile?.sensorType || 'N/A'"></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-400">Technologie</div>
                                <div class="text-white font-medium" x-text="(hardwareConfig?.activeProfile?.isCmos ? 'CMOS' : 'CCD') || 'N/A'"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="bg-white/5 rounded-lg p-4 mb-4 border border-white/10">
                        <h3 class="text-lg font-bold text-white mb-3">🎨 Filtres Configurés</h3>
                        <div class="space-y-2">
                            <template x-for="filter in hardwareConfig?.activeProfile?.filters || []" :key="filter.index">
                                <div class="flex items-center justify-between bg-white/5 rounded-lg p-3 border border-white/10">
                                    <div class="flex items-center gap-3">
                                        <span class="px-3 py-1 rounded bg-indigo-600 text-white font-mono text-sm"
                                              x-text="`#${filter.index}`"></span>
                                        <span class="text-white font-medium" x-text="filter.name"></span>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-400">Offset:</span>
                                            <span class="text-white font-medium" x-text="filter.offset"></span>
                                        </div>
                                        <div x-show="filter.magMin !== null && filter.magMax !== null">
                                            <span class="text-gray-400">Magnitude:</span>
                                            <span class="text-white font-medium" x-text="`${filter.magMin} - ${filter.magMax}`"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="!hardwareConfig?.activeProfile?.filters?.length" class="text-center text-gray-400 py-4">
                                Aucun filtre configuré
                            </div>
                        </div>
                    </div>

                    <!-- Modes de Lecture -->
                    <div class="bg-white/5 rounded-lg p-4 mb-4 border border-white/10">
                        <h3 class="text-lg font-bold text-white mb-3">📖 Modes de Lecture</h3>
                        <div class="grid grid-cols-3 gap-3">
                            <template x-for="mode in hardwareConfig?.activeProfile?.readoutModes || []" :key="mode.index">
                                <div class="bg-white/5 rounded-lg p-3 border border-white/10">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 rounded bg-purple-600 text-white font-mono text-xs"
                                              x-text="`#${mode.index}`"></span>
                                        <span class="text-white text-sm" x-text="mode.name"></span>
                                    </div>
                                </div>
                            </template>
                            <div x-show="!hardwareConfig?.activeProfile?.readoutModes?.length" class="col-span-3 text-center text-gray-400 py-4">
                                Aucun mode de lecture configuré
                            </div>
                        </div>
                    </div>

                    <!-- Vitesses -->
                    <div class="bg-white/5 rounded-lg p-4 mb-4 border border-white/10" x-show="hardwareConfig?.activeProfile?.speeds?.length">
                        <h3 class="text-lg font-bold text-white mb-3">⚡ Vitesses de Téléchargement</h3>
                        <div class="grid grid-cols-3 gap-3">
                            <template x-for="speed in hardwareConfig?.activeProfile?.speeds || []" :key="speed.index">
                                <div class="bg-white/5 rounded-lg p-3 border border-white/10">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 rounded bg-green-600 text-white font-mono text-xs"
                                              x-text="`#${speed.index}`"></span>
                                        <span class="text-white text-sm" x-text="speed.name"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Tous les Profils -->
                    <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                        <h3 class="text-lg font-bold text-white mb-3">📚 Tous les Profils Disponibles</h3>
                        <div class="space-y-2">
                            <template x-for="profile in hardwareConfig?.allProfiles || []" :key="profile.guid">
                                <div class="bg-white/5 rounded-lg p-3 border border-white/10"
                                     :class="profile.isActive ? 'border-green-500/50 bg-green-900/10' : ''">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span x-show="profile.isActive" class="text-green-400">✅</span>
                                            <span class="text-white font-medium" x-text="profile.name"></span>
                                        </div>
                                        <div class="flex items-center gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-400">Type:</span>
                                                <span class="text-white" x-text="profile.sensorType"></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-400">Filtres:</span>
                                                <span class="text-white" x-text="profile.filters?.length || 0"></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-400">Modes:</span>
                                                <span class="text-white" x-text="profile.readoutModes?.length || 0"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-700">
                <button @click="closeHardwareConfigModal()"
                        class="bg-gray-600 hover:bg-gray-700 px-6 py-2 rounded-lg text-white">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

<script>
    function voyagerControl() {
        return {
            sets: @json($initialSets),
            connected: @json($connectionStatus['success'] ?? false),
            loading: false,
            saving: false,
            showModal: false,
            showDetailsModal: false,
            showTargetsModal: false,
            showShotsModal: false,
            showHardwareConfigModal: false,
            modalMode: 'create', // 'create' or 'edit'
            selectedSet: null,
            selectedTarget: null,
            currentSetTargets: [],
            currentTargetShots: [],
            loadingTargets: false,
            loadingShots: false,
            loadingHardwareConfig: false,
            filterConfig: null,  // Pour mapper filterindex → nom de filtre
            hardwareConfig: null,  // Configuration matérielle complète
            searchQuery: '',
            filterStatus: 'all',
            filterProfile: '',
            formData: {
                name: '',
                profile_name: '',
                tag: '',
                note: '',
                status: 0,
                is_default: false
            },

            async init() {
                console.log('🔭 Voyager Control Panel initialized');
                console.log(`📊 ${this.sets.length} Sets chargés`);
                // Charger la configuration des filtres au démarrage
                await this.loadFilterConfig();
            },

            async loadFilterConfig() {
                try {
                    const response = await fetch('/admin/robotarget/api/config/filters', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.filterConfig = data.filters;
                        console.log('✅ Configuration des filtres chargée:', this.filterConfig);
                    }
                } catch (error) {
                    console.error('Erreur chargement config filtres:', error);
                }
            },

            get filteredSets() {
                return this.sets.filter(set => {
                    // Filter by search
                    if (this.searchQuery) {
                        const query = this.searchQuery.toLowerCase();
                        const matchName = set.setname.toLowerCase().includes(query);
                        const matchTag = (set.tag || '').toLowerCase().includes(query);
                        const matchProfile = set.profilename.toLowerCase().includes(query);
                        if (!matchName && !matchTag && !matchProfile) return false;
                    }

                    // Filter by status
                    if (this.filterStatus !== 'all') {
                        if (set.status !== parseInt(this.filterStatus)) return false;
                    }

                    // Filter by profile
                    if (this.filterProfile && set.profilename !== this.filterProfile) {
                        return false;
                    }

                    return true;
                });
            },

            async refreshSets() {
                this.loading = true;
                try {
                    const response = await fetch('/admin/robotarget/api/sets', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.sets = data.sets;
                        this.connected = true;
                        alert(`✅ ${data.count} Sets rechargés`);
                    } else {
                        alert('❌ Erreur: ' + (data.error || 'Erreur inconnue'));
                    }
                } catch (error) {
                    alert('❌ Erreur: ' + error.message);
                    this.connected = false;
                } finally {
                    this.loading = false;
                }
            },

            openCreateModal() {
                this.modalMode = 'create';
                this.formData = {
                    name: '',
                    profile_name: '',
                    tag: '',
                    note: '',
                    status: 0,
                    is_default: false
                };
                this.showModal = true;
            },

            editSet(set) {
                this.modalMode = 'edit';
                this.selectedSet = set;
                this.formData = {
                    guid: set.guid,
                    name: set.setname,
                    profile_name: set.profilename,
                    tag: set.tag || '',
                    note: set.note || '',
                    status: set.status,
                    is_default: set.isdefault
                };
                this.showModal = true;
            },

            async saveSet() {
                this.saving = true;
                try {
                    const url = this.modalMode === 'create'
                        ? '/admin/robotarget/api/sets'
                        : `/admin/robotarget/api/sets/${this.formData.guid}`;

                    const method = this.modalMode === 'create' ? 'POST' : 'PUT';

                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.formData)
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert(`✅ Set ${this.modalMode === 'create' ? 'créé' : 'modifié'} avec succès!`);
                        this.closeModal();
                        await this.refreshSets();
                    } else {
                        alert('❌ Erreur: ' + (data.error || 'Erreur inconnue'));
                    }
                } catch (error) {
                    alert('❌ Erreur: ' + error.message);
                } finally {
                    this.saving = false;
                }
            },

            async toggleSet(set) {
                const newStatus = set.status === 0 ? 1 : 0;
                const action = newStatus === 0 ? 'activer' : 'désactiver';

                if (!confirm(`Voulez-vous vraiment ${action} le Set "${set.setname}" ?`)) {
                    return;
                }

                try {
                    const response = await fetch(`/admin/robotarget/api/sets/${set.guid}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ enable: newStatus === 0 })
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert(`✅ Set ${action === 'activer' ? 'activé' : 'désactivé'} avec succès!`);
                        await this.refreshSets();
                    } else {
                        alert('❌ Erreur: ' + (data.error || 'Erreur inconnue'));
                    }
                } catch (error) {
                    alert('❌ Erreur: ' + error.message);
                }
            },

            async deleteSet(set) {
                if (!confirm(`⚠️ ATTENTION!\n\nVoulez-vous vraiment supprimer le Set "${set.setname}" ?\n\nCela supprimera également toutes les Targets et données associées!\n\nCette action est IRRÉVERSIBLE.`)) {
                    return;
                }

                try {
                    const response = await fetch(`/admin/robotarget/api/sets/${set.guid}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('✅ Set supprimé avec succès!');
                        await this.refreshSets();
                    } else {
                        alert('❌ Erreur: ' + (data.error || 'Erreur inconnue'));
                    }
                } catch (error) {
                    alert('❌ Erreur: ' + error.message);
                }
            },

            viewSet(set) {
                this.selectedSet = set;
                this.showDetailsModal = true;
            },

            closeModal() {
                this.showModal = false;
                this.selectedSet = null;
            },

            closeDetailsModal() {
                this.showDetailsModal = false;
                this.selectedSet = null;
            },

            async viewTargets(set) {
                this.selectedSet = set;
                this.loadingTargets = true;
                this.showTargetsModal = true;
                this.currentSetTargets = [];

                try {
                    const response = await fetch(`/admin/robotarget/api/sets/${set.guid}/targets`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await response.json();

                    if (data.success) {
                        this.currentSetTargets = data.targets;
                        console.log(`✅ ${data.count} Targets chargées pour le Set "${set.setname}"`);
                    } else {
                        alert('❌ Erreur: ' + (data.error || 'Erreur inconnue'));
                    }
                } catch (error) {
                    alert('❌ Erreur: ' + error.message);
                } finally {
                    this.loadingTargets = false;
                }
            },

            async viewShots(target) {
                this.selectedTarget = target;
                this.loadingShots = true;
                this.showShotsModal = true;
                this.currentTargetShots = [];

                try {
                    const response = await fetch(`/admin/robotarget/api/targets/${target.guid}/shots`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await response.json();

                    console.log('📊 Réponse API Shots:', data);

                    if (data.success) {
                        this.currentTargetShots = data.shots;
                        console.log(`✅ ${data.count} Shots chargés pour la Target "${target.targetname}"`);
                    } else {
                        const errorMsg = data.error || 'Erreur inconnue';
                        const debugInfo = data.debug ? '\n\nDebug: ' + JSON.stringify(data.debug, null, 2) : '';
                        alert('❌ Erreur: ' + errorMsg + debugInfo);
                        console.error('Erreur détaillée:', data);
                    }
                } catch (error) {
                    alert('❌ Erreur: ' + error.message);
                    console.error('Exception:', error);
                } finally {
                    this.loadingShots = false;
                }
            },

            closeTargetsModal() {
                this.showTargetsModal = false;
                this.selectedSet = null;
                this.currentSetTargets = [];
            },

            closeShotsModal() {
                this.showShotsModal = false;
                this.selectedTarget = null;
                this.currentTargetShots = [];
            },

            async viewHardwareConfig() {
                this.showHardwareConfigModal = true;
                this.loadingHardwareConfig = true;
                this.hardwareConfig = null;

                try {
                    console.log('🔧 Chargement de la configuration matérielle...');
                    const response = await fetch('/admin/robotarget/api/config/hardware', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    const data = await response.json();
                    console.log('📊 Réponse API Hardware Config:', data);

                    if (data.success) {
                        this.hardwareConfig = data.parsed;
                        console.log('✅ Configuration chargée:', this.hardwareConfig);
                    } else {
                        alert('❌ Erreur: ' + (data.error || 'Impossible de charger la configuration'));
                    }
                } catch (error) {
                    console.error('Erreur chargement config matérielle:', error);
                    alert('❌ Erreur: ' + error.message);
                } finally {
                    this.loadingHardwareConfig = false;
                }
            },

            closeHardwareConfigModal() {
                this.showHardwareConfigModal = false;
                this.hardwareConfig = null;
            },

            getFilterName(filterIndex) {
                if (!this.filterConfig || !this.filterConfig[filterIndex]) {
                    return `Filter ${filterIndex}`;
                }

                // Si c'est un objet avec une propriété 'name'
                if (typeof this.filterConfig[filterIndex] === 'object' && this.filterConfig[filterIndex].name) {
                    return this.filterConfig[filterIndex].name;
                }

                // Si c'est une chaîne simple
                return this.filterConfig[filterIndex];
            },

            formatExposure(seconds) {
                if (seconds >= 60) {
                    const minutes = Math.floor(seconds / 60);
                    const secs = seconds % 60;
                    return secs > 0 ? `${minutes}m ${secs}s` : `${minutes}m`;
                }
                return `${seconds}s`;
            }
        }
    }
</script>
@endsection
