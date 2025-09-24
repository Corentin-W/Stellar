{{-- resources/views/credits/history.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Historique des Crédits - AstroSphere')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900/50 to-slate-900 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(120,119,198,0.1),transparent_50%)]"></div>

    <div class="relative z-10 max-w-6xl mx-auto px-6 py-12">

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Historique des Crédits</h1>
                <p class="text-white/60 mt-2">Suivez toutes vos transactions de crédits</p>
            </div>
            <a href="{{ route('credits.shop') }}"
               class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition-all">
                Acheter des crédits
            </a>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Solde Actuel</p>
                        <p class="text-2xl font-bold text-white">{{ number_format($stats['current_balance']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Total Acheté</p>
                        <p class="text-2xl font-bold text-green-400">{{ number_format($stats['total_purchased']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Total Utilisé</p>
                        <p class="text-2xl font-bold text-orange-400">{{ number_format($stats['total_used']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Transactions</p>
                        <p class="text-2xl font-bold text-purple-400">{{ number_format($stats['transactions_count']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des transactions -->
        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h3 class="text-xl font-bold text-white">Historique des Transactions</h3>
                    <p class="text-sm text-white/60 mt-1">Vos opérations les plus récentes, triées du plus récent au plus ancien</p>
                </div>
                <div class="flex items-center gap-2 text-sm text-white/60">
                    <span class="hidden sm:inline">Total :</span>
                    <span class="px-3 py-1 rounded-full border border-white/10 text-white">{{ number_format($transactions->total()) }} transactions</span>
                </div>
            </div>

            @if($transactions->count() > 0)
                <div class="divide-y divide-white/10">
                    @foreach($transactions as $transaction)
                        <div class="p-6 hover:bg-white/5 transition-colors">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-white/5 border border-white/10">
                                        <svg class="w-6 h-6 {{ $transaction->type_color_class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $transaction->type_icon }}"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span class="text-lg font-semibold text-white">{{ $transaction->formatted_type }}</span>
                                            <span class="text-xs uppercase tracking-wide px-3 py-1 rounded-full border border-white/10 text-white/60">#{{ $transaction->id }}</span>
                                            <span class="text-sm text-white/60">{{ $transaction->formatted_date }}</span>
                                        </div>

                                        @if($transaction->description)
                                            <p class="mt-2 text-sm text-white/70">{{ $transaction->description }}</p>
                                        @endif

                                        <div class="mt-4 flex flex-wrap items-center gap-3 text-sm text-white/60">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V7a2 2 0 012-2h4l2-2h6a2 2 0 012 2v14a2 2 0 01-2 2z"/>
                                                </svg>
                                                Solde :
                                                <span class="text-white font-medium">{{ $transaction->formatted_balance_before }}</span>
                                                <svg class="w-4 h-4 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                                <span class="text-white font-semibold">{{ $transaction->formatted_balance_after }}</span>
                                            </span>

                                            @if($transaction->creditPackage)
                                                <span class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2h-2.586a1 1 0 01-.707-.293l-1.414-1.414A1 1 0 0012.586 2H8a2 2 0 00-2 2v9"/>
                                                    </svg>
                                                    Pack :
                                                    <span class="text-white font-semibold">{{ $transaction->creditPackage->name }}</span>
                                                    <span class="text-white/40">({{ number_format($transaction->creditPackage->total_credits) }} crédits)</span>
                                                </span>
                                            @endif

                                            @if($transaction->short_stripe_id)
                                                <span class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                                    </svg>
                                                    Paiement Stripe :
                                                    <span class="text-white font-medium">{{ $transaction->short_stripe_id }}</span>
                                                </span>
                                            @endif

                                            @if($transaction->reference_type && $transaction->reference_id)
                                                <span class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v6"/>
                                                    </svg>
                                                    Réf. :
                                                    <span class="text-white font-medium">{{ class_basename($transaction->reference_type) }} #{{ $transaction->reference_id }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end gap-3 text-right">
                                    <div class="text-2xl font-bold {{ $transaction->amount_color_class }}">{{ $transaction->formatted_amount }}</div>
                                    <div class="text-xs uppercase tracking-wide text-white/40">Crédits</div>
                                    <div class="flex items-center gap-2 text-sm text-white/60">
                                        <span class="text-white/40">Solde actuel</span>
                                        <span class="text-white font-semibold">{{ $transaction->formatted_balance_after }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-6 py-4 border-t border-white/10">
                    {{ $transactions->links() }}
                </div>
            @else
                <div class="p-12 text-center text-white/60">
                   
                    <h4 class="text-xl font-semibold text-white mb-2">Aucune transaction pour le moment</h4>
                    <p class="text-white/60 mb-6">Effectuez un achat de crédits pour voir apparaître l'historique de vos opérations.</p>
                    <a href="{{ route('credits.shop') }}" class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition-all">
                        Visiter la boutique
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
