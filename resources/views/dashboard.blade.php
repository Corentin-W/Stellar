@extends('layouts.astral-app')

@section('title', 'Dashboard')

@section('content')
<div class="p-6 lg:p-8" x-data="dashboardManager()" x-init="init()">

    <!-- Welcome Section -->
    <div class="mb-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h1 class="text-2xl lg:text-2xl font-bold text-white mb-1">
                    🌌 Bienvenue, {{ $user->name }}
                </h1>
                <p class="text-gray-400 text-sm">
                    Votre centre de contrôle astrophotographique
                </p>
            </div>

            @if($subscription && $subscription->isActive())
            <div class="flex items-center gap-3">
                <div class="bg-gradient-to-br from-purple-500/20 to-pink-500/20 border border-purple-500/30 rounded-lg px-4 py-2.5 transition-all hover:border-purple-500/50 hover:shadow-lg hover:shadow-purple-500/20 group">
                    <div class="text-[10px] text-gray-400 mb-0.5 uppercase tracking-wide font-medium">Abonnement</div>
                    <div class="text-base font-bold text-white">{{ $subscription->getPlanName() }}</div>
                    <div class="text-xs text-purple-300 mt-0.5">{{ $user->credits_balance }} crédits</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <!-- Crédits Disponibles -->
        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-md p-4 hover:border-purple-500/50 transition-all hover:shadow-lg hover:shadow-purple-500/10 group cursor-pointer">
            <div class="flex items-center justify-between mb-2.5">
                <h3 class="font-semibold text-white text-sm">💰 Crédits</h3>
                <svg class="w-4 h-4 text-purple-400 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-white mb-1">{{ number_format($user->credits_balance) }}</div>
            <div class="text-xs text-gray-400">
                @if($stats['credits_used_this_month'] > 0)
                    <span class="text-orange-400 font-medium">-{{ $stats['credits_used_this_month'] }}</span> ce mois
                @else
                    Aucune dépense ce mois
                @endif
            </div>
            <a href="{{ route('subscriptions.manage', ['locale' => app()->getLocale()]) }}"
               class="text-[10px] text-purple-400 hover:text-purple-300 mt-1.5 inline-block font-medium uppercase tracking-wide transition-transform hover:translate-x-1">
                Gérer →
            </a>
        </div>

        <!-- Total Targets -->
        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-md p-4 hover:border-blue-500/50 transition-all hover:shadow-lg hover:shadow-blue-500/10 group cursor-pointer">
            <div class="flex items-center justify-between mb-2.5">
                <h3 class="font-semibold text-white text-sm">🎯 Targets</h3>
                <svg class="w-4 h-4 text-blue-400 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-white mb-1">{{ $stats['total_targets'] }}</div>
            <div class="text-xs text-gray-400">
                @if($stats['active_targets'] > 0)
                    <span class="text-green-400 font-medium">{{ $stats['active_targets'] }}</span> en cours
                @else
                    Aucune active
                @endif
            </div>
            <a href="{{ route('robotarget.index', ['locale' => app()->getLocale()]) }}"
               class="text-[10px] text-blue-400 hover:text-blue-300 mt-1.5 inline-block font-medium uppercase tracking-wide transition-transform hover:translate-x-1">
                Voir mes targets →
            </a>
        </div>

        <!-- Sessions Complétées -->
        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-md p-4 hover:border-green-500/50 transition-all hover:shadow-lg hover:shadow-green-500/10 group cursor-pointer">
            <div class="flex items-center justify-between mb-2.5">
                <h3 class="font-semibold text-white text-sm">✅ Sessions</h3>
                <svg class="w-4 h-4 text-green-400 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-white mb-1">{{ $stats['completed_sessions'] }}</div>
            <div class="text-xs text-gray-400">
                Sessions complétées
            </div>
            <div class="text-[10px] text-green-400 mt-1.5 font-medium">
                {{ number_format($stats['total_exposure_seconds'] / 3600, 1) }}h d'exposition totale
            </div>
        </div>

        <!-- Images Capturées -->
        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-md p-4 hover:border-pink-500/50 transition-all hover:shadow-lg hover:shadow-pink-500/10 group cursor-pointer">
            <div class="flex items-center justify-between mb-2.5">
                <h3 class="font-semibold text-white text-sm">📸 Images</h3>
                <svg class="w-4 h-4 text-pink-400 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-white mb-1">{{ number_format($stats['total_images']) }}</div>
            <div class="text-xs text-gray-400">
                Images acceptées
            </div>
            <a href="{{ route('robotarget.gallery', ['locale' => app()->getLocale()]) }}"
               class="text-[10px] text-pink-400 hover:text-pink-300 mt-1.5 inline-block font-medium uppercase tracking-wide transition-transform hover:translate-x-1">
                Voir la galerie →
            </a>
        </div>

    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

        <!-- Recent Sessions -->
        <div class="lg:col-span-2 bg-white/5 backdrop-blur-sm border border-white/10 rounded-md p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-white">📊 Dernières Sessions</h3>
                <a href="{{ route('robotarget.index', ['locale' => app()->getLocale()]) }}"
                   class="text-xs text-blue-400 hover:text-blue-300 font-medium uppercase tracking-wide transition-transform hover:translate-x-1">
                    Voir tout →
                </a>
            </div>

            @if($recentSessions->count() > 0)
            <div class="space-y-2">
                @foreach($recentSessions as $session)
                <div class="flex items-center justify-between p-3 rounded-md bg-white/5 hover:bg-white/10 transition-all border border-transparent hover:border-white/20 group cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-md bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-medium text-white text-sm">{{ $session->target->target_name }}</h4>
                            <p class="text-gray-400 text-xs">
                                {{ $session->images_accepted }} images •
                                {{ number_format($session->total_duration / 3600, 1) }}h
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-400">
                            {{ $session->completed_at->format('d/m/Y') }}
                        </div>
                        <div class="text-[10px] text-green-400 mt-0.5 font-medium">
                            ✓ Complétée
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <div class="text-4xl mb-3">🌟</div>
                <h4 class="text-sm font-semibold text-white mb-1">Aucune session complétée</h4>
                <p class="text-gray-400 text-xs mb-3">Créez votre première target pour commencer</p>
                <a href="{{ route('robotarget.create', ['locale' => app()->getLocale()]) }}"
                   class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-medium text-xs rounded-md hover:opacity-90 transition">
                    ✨ Créer une Target
                </a>
            </div>
            @endif
        </div>

        <!-- Active Targets Sidebar -->
        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-md p-4">
            <h3 class="text-base font-semibold text-white mb-4">🔥 Targets Actives</h3>

            @if($activeTargets->count() > 0)
            <div class="space-y-2">
                @foreach($activeTargets as $target)
                <div class="p-3 rounded-md bg-white/5 hover:bg-white/10 transition-all border border-transparent hover:border-white/20 group cursor-pointer"
                     onclick="window.location='{{ route('robotarget.show', ['locale' => app()->getLocale(), 'guid' => $target->guid]) }}'">
                    <h4 class="font-medium text-white text-sm mb-1.5 group-hover:text-blue-400 transition-colors">{{ $target->target_name }}</h4>
                    <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
                        <span class="px-1.5 py-0.5 bg-green-500/20 text-green-400 rounded font-medium">En cours</span>
                        <span>{{ $target->created_at->diffForHumans() }}</span>
                    </div>
                    @if($target->sessions()->whereNull('result')->exists())
                    <div class="mt-3">
                        <div class="flex items-center gap-2 text-xs text-blue-400">
                            <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"></div>
                            Session en cours...
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <div class="text-4xl mb-3">🎯</div>
                <p class="text-gray-400 text-sm">Aucune target active</p>
            </div>
            @endif
        </div>

    </div>

    <!-- Filter Distribution & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        <!-- Filter Distribution -->
        @if($filterDistribution->count() > 0)
        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg p-6">
            <h3 class="text-xl font-semibold text-white mb-6">🎨 Distribution des Filtres</h3>

            <div class="space-y-3">
                @foreach($filterDistribution as $filter)
                @php
                    $total = $filterDistribution->sum('total_shots');
                    $percentage = ($filter->total_shots / $total) * 100;
                    $color = match($filter->filter_name) {
                        'L', 'Luminance' => 'bg-gray-500',
                        'R', 'Red' => 'bg-red-500',
                        'G', 'Green' => 'bg-green-500',
                        'B', 'Blue' => 'bg-blue-500',
                        'Ha', 'H-alpha' => 'bg-rose-500',
                        'OIII', 'O-III' => 'bg-cyan-500',
                        'SII', 'S-II' => 'bg-amber-500',
                        default => 'bg-purple-500'
                    };
                @endphp
                <div>
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="text-white font-medium">{{ $filter->filter_name }}</span>
                        <span class="text-gray-400">{{ $filter->total_shots }} poses ({{ number_format($percentage, 1) }}%)</span>
                    </div>
                    <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                        <div class="{{ $color }} h-full rounded-full transition-all duration-500"
                             style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="bg-gradient-to-br from-purple-500/10 to-pink-500/10 border border-purple-500/20 rounded-lg p-6">
            <h3 class="text-xl font-semibold text-white mb-6">⚡ Actions Rapides</h3>

            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('robotarget.create', ['locale' => app()->getLocale()]) }}"
                   class="flex flex-col items-center justify-center p-4 bg-white/5 hover:bg-white/10 rounded-lg transition group text-center">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <span class="text-white font-medium text-sm">Nouvelle Target</span>
                </a>

                <a href="{{ route('robotarget.gallery', ['locale' => app()->getLocale()]) }}"
                   class="flex flex-col items-center justify-center p-4 bg-white/5 hover:bg-white/10 rounded-lg transition group text-center">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-white font-medium text-sm">Ma Galerie</span>
                </a>

                <a href="{{ route('robotarget.index', ['locale' => app()->getLocale()]) }}"
                   class="flex flex-col items-center justify-center p-4 bg-white/5 hover:bg-white/10 rounded-lg transition group text-center">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span class="text-white font-medium text-sm">Mes Targets</span>
                </a>

                <a href="{{ route('subscriptions.manage', ['locale' => app()->getLocale()]) }}"
                   class="flex flex-col items-center justify-center p-4 bg-white/5 hover:bg-white/10 rounded-lg transition group text-center">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <span class="text-white font-medium text-sm">Abonnement</span>
                </a>
            </div>

            @if(!$subscription || !$subscription->isActive())
            <div class="mt-6 p-4 bg-amber-500/20 border border-amber-500/30 rounded-lg">
                <p class="text-amber-300 text-sm mb-3">
                    ⚠️ Aucun abonnement actif
                </p>
                <a href="{{ route('subscriptions.manage', ['locale' => app()->getLocale()]) }}"
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold rounded-lg hover:opacity-90 transition text-sm">
                    Choisir un abonnement
                </a>
            </div>
            @endif
        </div>

    </div>

    <!-- Welcome Message for First Time -->
    @if($stats['total_targets'] === 0)
    <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 border border-purple-500/30 rounded-lg p-8 text-center">
        <div class="text-6xl mb-4">🚀</div>
        <h3 class="text-2xl font-bold text-white mb-3">Bienvenue dans Stellar !</h3>
        <p class="text-gray-300 mb-6 max-w-2xl mx-auto">
            Vous êtes prêt à capturer les merveilles du cosmos. Créez votre première target et laissez notre télescope
            capturer des images époustouflantes du ciel profond pendant que vous dormez !
        </p>
        <a href="{{ route('robotarget.create', ['locale' => app()->getLocale()]) }}"
           class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold rounded-lg hover:opacity-90 transition text-lg">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Créer ma Première Target
        </a>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function dashboardManager() {
    return {
        init() {
            console.log('✨ Dashboard initialized');
        }
    };
}
</script>
@endpush
