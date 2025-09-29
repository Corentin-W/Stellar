@extends('layouts.astral-app')

@section('title', 'Gestion des Réservations')

@section('content')
<div class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">🎫 Gestion des Réservations</h1>
                <p class="text-white/60">Validez et gérez les réservations d'équipement</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.bookings.calendar') }}" class="px-6 py-3 rounded-lg bg-white/5 text-white hover:bg-white/10 transition-colors">
                    📅 Calendrier
                </a>
                <a href="{{ route('admin.bookings.blackouts') }}" class="px-6 py-3 rounded-lg bg-white/5 text-white hover:bg-white/10 transition-colors">
                    🚫 Blocages
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="dashboard-card p-4 mb-6 bg-green-500/20 border-green-500/50">
            <p class="text-green-400">✅ {{ session('success') }}</p>
        </div>
        @endif

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="dashboard-card p-6">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-yellow-400 text-sm font-medium">⏳ En attente</div>
                    <div class="w-12 h-12 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-white">{{ $stats['pending'] }}</div>
                <div class="text-white/60 text-sm mt-1">À valider</div>
            </div>

            <div class="dashboard-card p-6">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-green-400 text-sm font-medium">✅ Confirmées</div>
                    <div class="w-12 h-12 rounded-lg bg-green-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-white">{{ $stats['confirmed'] }}</div>
                <div class="text-white/60 text-sm mt-1">Actives</div>
            </div>

            <div class="dashboard-card p-6">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-blue-400 text-sm font-medium">📅 Aujourd'hui</div>
                    <div class="w-12 h-12 rounded-lg bg-blue-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-white">{{ $stats['total_today'] }}</div>
                <div class="text-white/60 text-sm mt-1">Total</div>
            </div>

            <div class="dashboard-card p-6">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-purple-400 text-sm font-medium">💰 Revenus</div>
                    <div class="w-12 h-12 rounded-lg bg-purple-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['total_revenue']) }}</div>
                <div class="text-white/60 text-sm mt-1">Crédits</div>
            </div>
        </div>

        <!-- Réservations en attente -->
        @if($pendingBookings->count() > 0)
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-white mb-4">⏳ Réservations en attente de validation</h2>
            <div class="space-y-4">
                @foreach($pendingBookings as $booking)
                <div class="dashboard-card p-6 hover:scale-[1.01] transition-transform">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <h3 class="text-xl font-bold text-white">{{ $booking->equipment->name }}</h3>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-500 text-white">
                                    ⏳ En attente
                                </span>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
                                <div>
                                    <div class="text-white/60 mb-1">Utilisateur</div>
                                    <div class="text-white font-medium">{{ $booking->user->name }}</div>
                                </div>
                                <div>
                                    <div class="text-white/60 mb-1">Date</div>
                                    <div class="text-white font-medium">{{ $booking->start_datetime->format('d/m/Y H:i') }}</div>
                                </div>
                                <div>
                                    <div class="text-white/60 mb-1">Durée</div>
                                    <div class="text-white font-medium">{{ $booking->getDurationInHours() }}h</div>
                                </div>
                                <div>
                                    <div class="text-white/60 mb-1">Coût</div>
                                    <div class="text-yellow-400 font-semibold">{{ number_format($booking->credits_cost) }} crédits</div>
                                </div>
                            </div>

                            @if($booking->user_notes)
                            <div class="p-3 bg-white/5 rounded-lg">
                                <div class="text-white/60 text-xs mb-1">Notes de l'utilisateur</div>
                                <div class="text-white text-sm">{{ $booking->user_notes }}</div>
                            </div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-2">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="px-4 py-2 rounded-lg bg-blue-500/20 text-blue-400 hover:bg-blue-500/30 transition-colors text-sm font-medium text-center">
                                👁️ Voir
                            </a>
                            <form action="{{ route('admin.bookings.confirm', $booking) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 rounded-lg bg-green-500/20 text-green-400 hover:bg-green-500/30 transition-colors text-sm font-medium">
                                    ✅ Valider
                                </button>
                            </form>
                            <button onclick="openRejectModal({{ $booking->id }})" class="px-4 py-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors text-sm font-medium">
                                ❌ Rejeter
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($pendingBookings->hasPages())
            <div class="mt-6">
                {{ $pendingBookings->links() }}
            </div>
            @endif
        </div>
        @endif

        <!-- Réservations récentes -->
        <div>
            <h2 class="text-2xl font-bold text-white mb-4">📋 Réservations récentes</h2>
            <div class="dashboard-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="text-left p-4 text-white/60 font-medium">Utilisateur</th>
                                <th class="text-left p-4 text-white/60 font-medium">Équipement</th>
                                <th class="text-left p-4 text-white/60 font-medium">Date</th>
                                <th class="text-left p-4 text-white/60 font-medium">Durée</th>
                                <th class="text-left p-4 text-white/60 font-medium">Statut</th>
                                <th class="text-left p-4 text-white/60 font-medium">Coût</th>
                                <th class="text-right p-4 text-white/60 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBookings as $booking)
                            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                <td class="p-4 text-white">{{ $booking->user->name }}</td>
                                <td class="p-4 text-white">{{ $booking->equipment->name }}</td>
                                <td class="p-4 text-white/70">{{ $booking->start_datetime->format('d/m/Y H:i') }}</td>
                                <td class="p-4 text-white/70">{{ $booking->getDurationInHours() }}h</td>
                                <td class="p-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold text-white {{ $booking->getStatusBadgeClass() }}">
                                        {{ $booking->getStatusLabel() }}
                                    </span>
                                </td>
                                <td class="p-4 text-yellow-400 font-semibold">{{ number_format($booking->credits_cost) }}</td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('admin.bookings.show', $booking) }}" class="text-blue-400 hover:text-blue-300 transition-colors">
                                        Voir →
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal de rejet -->
<div id="reject-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" x-data="{ show: false }" x-show="show" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="show = false"></div>

        <div class="relative bg-gradient-to-br from-gray-900/90 to-gray-800/90 backdrop-blur-xl border border-white/10 rounded-2xl p-8 max-w-lg w-full shadow-2xl">
            <h3 class="text-2xl font-bold text-white mb-4">❌ Rejeter la réservation</h3>

            <form id="reject-form" method="POST">
                @csrf

                <div class="mb-6">
                    <label class="block text-white font-medium mb-2">Raison du rejet *</label>
                    <textarea name="rejection_reason" rows="4" required class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-white/40 focus:border-red-500 focus:ring-2 focus:ring-red-500/20" placeholder="Expliquez pourquoi vous rejetez cette réservation..."></textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-white font-medium mb-2">Notes administrateur (optionnel)</label>
                    <textarea name="admin_notes" rows="3" class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-white/40 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20" placeholder="Notes internes..."></textarea>
                </div>

                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 mb-6">
                    <p class="text-yellow-400 text-sm">
                        ℹ️ Les crédits seront automatiquement remboursés à l'utilisateur.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="show = false" class="flex-1 px-6 py-3 rounded-lg bg-white/5 text-white hover:bg-white/10 transition-colors">
                        Annuler
                    </button>
                    <button type="submit" class="flex-1 px-6 py-3 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition-all">
                        Confirmer le rejet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openRejectModal(bookingId) {
    const modal = document.getElementById('reject-modal');
const form = document.getElementById('reject-form');
form.action = /admin/bookings/${bookingId}/reject;
modal.classList.remove('hidden');
setTimeout(() => {
    modal.querySelector('[x-data]').__x.$data.show = true;
}, 10);
}
</script>
@endpush
