{{-- resources/views/credits/success.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Achat Réussi - AstroSphere')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900/50 to-slate-900 relative overflow-hidden flex items-center justify-center">
    <!-- Background effects -->
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(120,119,198,0.1),transparent_50%)]"></div>

    <div class="relative z-10 max-w-md mx-auto px-6 text-center">

        <!-- Animation de succès -->
        <div class="mb-8 relative">
            <div class="w-24 h-24 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full mx-auto flex items-center justify-center mb-4 {{ $already_processed ?? false ? 'animate-pulse' : 'animate-bounce' }}">
                @if($already_processed ?? false)
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @else
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                @endif
            </div>

            <!-- Particules de succès -->
            @if(!($already_processed ?? false))
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-4 left-8 w-2 h-2 bg-yellow-400 rounded-full animate-bounce" style="animation-delay: 0.1s;"></div>
                <div class="absolute top-6 right-6 w-1.5 h-1.5 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 0.3s;"></div>
                <div class="absolute bottom-8 left-12 w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0.5s;"></div>
                <div class="absolute bottom-6 right-8 w-1.5 h-1.5 bg-pink-400 rounded-full animate-bounce" style="animation-delay: 0.7s;"></div>
            </div>
            @endif
        </div>

        <!-- Message de succès ou d'information -->
        @if($already_processed ?? false)
            <h1 class="text-3xl font-bold text-yellow-400 mb-4">
                Transaction Déjà Traitée
            </h1>
            <div class="bg-yellow-500/20 border border-yellow-500/30 rounded-lg p-4 mb-6">
                <p class="text-yellow-300">{{ $message ?? 'Cette transaction a déjà été traitée.' }}</p>
            </div>
        @else
            <h1 class="text-3xl font-bold text-white mb-4">
                Achat Confirmé !
            </h1>
            <div class="bg-green-500/20 border border-green-500/30 rounded-lg p-4 mb-6">
                <p class="text-green-300">{{ $message ?? 'Vos crédits ont été ajoutés avec succès !' }}</p>
            </div>
        @endif

        <!-- Informations sur l'achat -->
        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6 mb-8">
            <div class="space-y-4">

                @if(!($already_processed ?? false) && isset($credits_added) && $credits_added > 0)
                <!-- Afficher les crédits ajoutés pour les nouveaux paiements -->
                <div class="flex items-center justify-between">
                    <span class="text-white/70">Crédits ajoutés</span>
                    <span class="text-2xl font-bold text-green-400">+{{ number_format($credits_added) }}</span>
                </div>
                <div class="border-t border-white/10 pt-4"></div>
                @endif

              {{--   <!-- Solde actuel -->
                <div class="flex items-center justify-between">
                    <span class="text-white/70">Votre solde actuel</span>
                    <span class="text-xl font-bold text-white">{{ number_format(auth()->user()->credits_balance) }} crédits</span>
                </div> --}}

                @if(isset($session) && isset($session->amount_total))
                <div class="flex items-center justify-between">
                    <span class="text-white/70">Montant payé</span>
                    <span class="text-white font-semibold">{{ number_format($session->amount_total / 100, 2) }}€</span>
                </div>
                @endif

                @if(!($already_processed ?? false))
                <div class="border-t border-white/10 pt-4">
                    <div class="flex items-center gap-3 text-green-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="font-medium">Crédits disponibles immédiatement</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Informations de session (debug - à retirer en production) -->
        @if(config('app.debug') && isset($session))
        <div class="bg-gray-800/50 border border-gray-600 rounded-lg p-3 mb-6 text-xs text-gray-400">
            <p><strong>Session ID:</strong> {{ $session->id }}</p>
            @if(isset($credits_added))
            <p><strong>Crédits ajoutés:</strong> {{ $credits_added }}</p>
            @endif
            <p><strong>Déjà traité:</strong> {{ ($already_processed ?? false) ? 'Oui' : 'Non' }}</p>
        </div>
        @endif

        <!-- Actions suivantes -->
        <div class="space-y-4">
            <a href="{{ route('dashboard') }}"
               class="w-full inline-flex items-center justify-center gap-3 py-4 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Retour au Dashboard
            </a>

            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('credits.history') }}"
                   class="inline-flex items-center justify-center gap-2 py-3 bg-white/10 hover:bg-white/20 text-white font-medium rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Historique
                </a>

                <a href="{{ route('credits.shop') }}"
                   class="inline-flex items-center justify-center gap-2 py-3 bg-white/10 hover:bg-white/20 text-white font-medium rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                    Acheter plus
                </a>
            </div>
        </div>

        <!-- Message d'aide -->
        <div class="mt-8 text-center">
            <p class="text-white/60 text-sm">
                Une question ? <a href="{{ route('support.create') }}" class="text-blue-400 hover:text-blue-300 transition-colors">Contactez notre support</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Actualiser le solde dans la sidebar si elle existe
document.addEventListener('DOMContentLoaded', function() {
    const sidebarBalance = document.querySelector('#sidebar-balance');
    if (sidebarBalance) {
        sidebarBalance.textContent = '{{ number_format(auth()->user()->credits_balance) }} crédits';
    }
});
</script>
@endpush
