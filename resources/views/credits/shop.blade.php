{{-- resources/views/credits/shop.blade.php - Version redirection --}}
@extends('layouts.astral-app')

@section('title', 'Boutique de Crédits - AstroSphere')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900/50 to-slate-900 relative overflow-hidden" x-data="creditShop()">
    <!-- Background effects -->
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(120,119,198,0.1),transparent_50%)]"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 py-12">

        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl lg:text-5xl font-black text-white mb-4">
                Boutique de <span class="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">Crédits</span>
            </h1>
            <p class="text-xl text-white/70 max-w-2xl mx-auto">
                Rechargez votre compte avec nos packages de crédits et profitez de nos offres spéciales
            </p>

            <!-- Balance actuelle -->
            <div class="inline-flex items-center gap-3 bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl px-6 py-3 mt-6">
                <div class="w-8 h-8 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm text-white/60">Votre solde actuel</div>
                    <div class="text-xl font-bold text-white">{{ number_format(auth()->user()->credits_balance) }} crédits</div>
                </div>
            </div>
        </div>

        <!-- Packages de crédits -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-white mb-8 text-center">Choisissez votre package</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($packages as $package)
                <div class="relative group">
                    @if($package->is_featured)
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 z-10">
                        <span class="bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs font-bold px-4 py-2 rounded-full">
                            POPULAIRE
                        </span>
                    </div>
                    @endif

                    <form action="{{ route('credits.checkout') }}" method="POST" class="h-full">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $package->id }}">

                        <div class="package-card relative bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6 h-full transition-all duration-300 hover:bg-white/10 hover:border-white/20 hover:scale-105 cursor-pointer {{ $package->is_featured ? 'ring-2 ring-purple-500/50' : '' }}">

                            <!-- Header du package -->
                            <div class="text-center mb-6">
                                <h3 class="text-xl font-bold text-white mb-2">{{ $package->name }}</h3>
                                <p class="text-white/60 text-sm">{{ $package->description }}</p>
                            </div>

                            <!-- Prix principal -->
                            <div class="text-center mb-6">
                                <div class="text-4xl font-black text-white mb-2">
                                    {{ number_format($package->price_euros, 2) }}€
                                </div>
                                <div class="text-sm text-white/60">
                                    {{ number_format($package->credit_value, 3) }}€ par crédit
                                </div>
                            </div>

                            <!-- Détails des crédits -->
                            <div class="space-y-3 mb-6">
                                <div class="flex items-center justify-between py-2 border-b border-white/10">
                                    <span class="text-white/70">Crédits de base</span>
                                    <span class="text-white font-semibold">{{ number_format($package->credits_amount) }}</span>
                                </div>

                                @if($package->bonus_credits > 0)
                                <div class="flex items-center justify-between py-2 border-b border-white/10">
                                    <span class="text-green-400">Crédits bonus</span>
                                    <span class="text-green-400 font-semibold">+{{ number_format($package->bonus_credits) }}</span>
                                </div>
                                @endif

                                <div class="flex items-center justify-between py-2 border-b border-white/10 font-bold">
                                    <span class="text-white">Total</span>
                                    <span class="text-white text-lg">{{ number_format($package->total_credits) }}</span>
                                </div>
                            </div>

                            <!-- Code promo (optionnel) -->
                            <div class="mb-4">
                                <input type="text"
                                       name="promotion_code"
                                       placeholder="Code promo (optionnel)"
                                       class="w-full bg-white/5 border border-white/20 rounded-lg px-3 py-2 text-white placeholder-white/50 focus:outline-none focus:border-blue-400 text-sm">
                            </div>

                            <!-- Bouton d'achat -->
                            <button type="submit" class="w-full py-3 px-6 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 hover:scale-105 group-hover:shadow-lg group-hover:shadow-purple-500/25">
                                Acheter maintenant
                            </button>
                        </div>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function creditShop() {
    return {
        init() {
            console.log('Credit Shop with Checkout initialized');
        }
    }
}
</script>
@endpush
