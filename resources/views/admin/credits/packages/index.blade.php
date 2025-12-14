{{-- resources/views/admin/credits/packages/index.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Gestion des Packages - Admin')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900/50 to-slate-900 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(120,119,198,0.1),transparent_50%)]"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 py-8">

      

        <!-- Statistiques rapides -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Total Packages</p>
                        <p class="text-2xl font-bold text-white">{{ $packages->total() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4-8-4m16 0v10l-8 4-8-4V7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Packages Actifs</p>
                        <p class="text-2xl font-bold text-green-400">{{ $packages->where('is_active', true)->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Packages Populaires</p>
                        <p class="text-2xl font-bold text-purple-400">{{ $packages->where('is_featured', true)->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Revenus Estimés</p>
                        <p class="text-2xl font-bold text-yellow-400">{{ number_format($packages->sum('price_cents') / 100, 2) }}€</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des packages -->
        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <h3 class="text-xl font-bold text-white">Packages de Crédits</h3>
            </div>

            @if($packages->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-white/5">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-white/80">Nom</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-white/80">Crédits</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-white/80">Prix</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-white/80">Statut</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-white/80">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10" x-data="packagesManager()">
                        @foreach($packages as $package)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">{{ number_format($package->credits_amount / 1000, 1) }}K</span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-semibold text-white">{{ $package->name }}</h4>
                                            @if($package->is_featured)
                                                <span class="bg-purple-500 text-white text-xs px-2 py-1 rounded-full">Populaire</span>
                                            @endif
                                        </div>
                                        <p class="text-white/60 text-sm">{{ Str::limit($package->description, 50) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-white">
                                    <div class="font-semibold">{{ number_format($package->credits_amount) }}</div>
                                    @if($package->bonus_credits > 0)
                                        <div class="text-green-400 text-sm">+{{ number_format($package->bonus_credits) }} bonus</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-white">
                                    <div class="font-semibold">{{ number_format($package->price_euros, 2) }}€</div>
                                    <div class="text-white/60 text-sm">{{ number_format($package->credit_value, 3) }}€/crédit</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox"
                                               @change="togglePackageStatus({{ $package->id }}, $event.target.checked)"
                                               {{ $package->is_active ? 'checked' : '' }}
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                                    </label>
                                    <span class="text-sm {{ $package->is_active ? 'text-green-400' : 'text-red-400' }}">
                                        {{ $package->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.credits.packages.edit', $package) }}"
                                       class="p-2 bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 rounded-lg transition-colors"
                                       title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <button @click="deletePackage({{ $package->id }}, '{{ $package->name }}')"
                                            class="p-2 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg transition-colors"
                                            title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($packages->hasPages())
            <div class="px-6 py-4 border-t border-white/10">
                {{ $packages->links() }}
            </div>
            @endif

            @else
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-white/5 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4-8-4m16 0v10l-8 4-8-4V7"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Aucun package trouvé</h3>
                <p class="text-white/60 mb-6">Créez votre premier package de crédits pour commencer</p>
                <a href="{{ route('admin.credits.packages.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Créer un package
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function packagesManager() {
    return {
        async togglePackageStatus(packageId, isActive) {
            try {
                const response = await fetch(`/admin/credits/packages/${packageId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ is_active: isActive })
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message || 'Erreur lors de la mise à jour');
                }

                this.showNotification(data.message, 'success');

            } catch (error) {
                console.error('Toggle status failed:', error);
                this.showNotification('Erreur lors de la mise à jour', 'error');
                // Revert checkbox
                event.target.checked = !isActive;
            }
        },

        async deletePackage(packageId, packageName) {
            if (!confirm(`Êtes-vous sûr de vouloir supprimer le package "${packageName}" ?`)) {
                return;
            }

            try {
                const response = await fetch(`/admin/credits/packages/${packageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    this.showNotification('Package supprimé avec succès', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error('Erreur lors de la suppression');
                }

            } catch (error) {
                console.error('Delete failed:', error);
                this.showNotification('Erreur lors de la suppression', 'error');
            }
        },

        showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-6 right-6 px-6 py-4 rounded-xl shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            } text-white`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    }
}
</script>
@endpush
