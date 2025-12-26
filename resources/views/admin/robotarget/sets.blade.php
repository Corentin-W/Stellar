{{-- resources/views/admin/robotarget/sets.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'RoboTarget Sets Manager')

@section('content')
<div x-data="setsManager()" x-init="init()">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-900 to-purple-900 rounded-xl p-6 shadow-2xl border border-white/10 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">🎯 RoboTarget Sets Manager</h1>
                <p class="text-gray-300 mt-1">Gestion complète des Sets Voyager</p>
            </div>
            <div class="flex items-center gap-4">
                <!-- Connection Status -->
                <div class="bg-gray-800 rounded-lg px-4 py-2">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full" :class="connected ? 'bg-green-500 animate-pulse' : 'bg-red-500'"></div>
                        <span class="text-sm text-white" x-text="connected ? 'Connecté' : 'Déconnecté'"></span>
                    </div>
                </div>
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
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

<script>
    function setsManager() {
        return {
            sets: @json($initialSets),
            connected: @json($connectionStatus['success'] ?? false),
            loading: false,
            saving: false,
            showModal: false,
            showDetailsModal: false,
            modalMode: 'create', // 'create' or 'edit'
            selectedSet: null,
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
                console.log('🎯 RoboTarget Sets Manager initialized');
                console.log(`📊 ${this.sets.length} Sets chargés`);
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
            }
        }
    }
</script>
@endsection
