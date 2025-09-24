{{-- resources/views/admin/credits/packages/create.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Créer un Package - Admin')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900/50 to-slate-900 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(120,119,198,0.1),transparent_50%)]"></div>

    <div class="relative z-10 max-w-4xl mx-auto px-6 py-8">

        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('admin.credits.packages.index') }}"
               class="p-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-white">Créer un Package</h1>
                <p class="text-white/60 mt-2">Configurez un nouveau package de crédits</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.credits.packages.store') }}" x-data="packageForm()" class="space-y-8">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Formulaire principal -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Informations de base -->
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Informations Générales</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Nom du package *</label>
                                <input type="text" name="name" required
                                       x-model="form.name"
                                       value="{{ old('name') }}"
                                       class="w-full bg-white/5 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-400 transition-colors"
                                       placeholder="Ex: Starter Pack">
                                @error('name')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Ordre de tri</label>
                                <input type="number" name="sort_order" min="0"
                                       value="{{ old('sort_order', 0) }}"
                                       class="w-full bg-white/5 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-400 transition-colors"
                                       placeholder="0">
                                @error('sort_order')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-white mb-2">Description</label>
                            <textarea name="description" rows="3"
                                      class="w-full bg-white/5 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-400 transition-colors"
                                      placeholder="Description du package...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Configuration des crédits et prix -->
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Crédits et Tarification</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Nombre de crédits *</label>
                                <input type="number" name="credits_amount" required min="1" max="50000"
                                       x-model="form.credits_amount"
                                       @input="updateCalculations()"
                                       value="{{ old('credits_amount') }}"
                                       class="w-full bg-white/5 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-400 transition-colors"
                                       placeholder="100">
                                @error('credits_amount')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Crédits bonus</label>
                                <input type="number" name="bonus_credits" min="0" max="10000"
                                       x-model="form.bonus_credits"
                                       @input="updateCalculations()"
                                       value="{{ old('bonus_credits', 0) }}"
                                       class="w-full bg-white/5 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-400 transition-colors"
                                       placeholder="0">
                                @error('bonus_credits')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Prix (€) *</label>
                                <input type="number" name="price_euros" required min="0.01" max="5000" step="0.01"
                                       x-model="form.price_euros"
                                       @input="updateCalculations()"
                                       value="{{ old('price_euros') }}"
                                       class="w-full bg-white/5 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-400 transition-colors"
                                       placeholder="9.99">
                                @error('price_euros')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Réduction (%)</label>
                                <input type="number" name="discount_percentage" min="0" max="100"
                                       value="{{ old('discount_percentage') }}"
                                       class="w-full bg-white/5 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-400 transition-colors"
                                       placeholder="0">
                                @error('discount_percentage')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Options</h3>

                        <div class="space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="is_active" checked
                                       class="w-4 h-4 text-blue-500 bg-white/10 border-white/30 rounded focus:ring-blue-500 focus:ring-2">
                                <span class="text-white">Package actif</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="is_featured"
                                       class="w-4 h-4 text-blue-500 bg-white/10 border-white/30 rounded focus:ring-blue-500 focus:ring-2">
                                <span class="text-white">Package populaire (mis en avant)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Aperçu en temps réel -->
                <div class="lg:col-span-1">
                    <div class="sticky top-8 space-y-6">

                        <!-- Aperçu du package -->
                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                            <h3 class="text-lg font-semibold text-white mb-4">Aperçu</h3>

                            <div class="bg-gradient-to-br from-blue-500/20 to-purple-600/20 border border-blue-500/30 rounded-xl p-4">
                                <h4 class="text-white font-semibold mb-2" x-text="form.name || 'Nom du package'"></h4>

                                <div class="text-3xl font-black text-white mb-2">
                                    <span x-text="form.price_euros ? parseFloat(form.price_euros).toFixed(2) : '0.00'"></span>€
                                </div>

                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-white/70">Crédits de base</span>
                                        <span class="text-white" x-text="form.credits_amount ? parseInt(form.credits_amount).toLocaleString() : '0'"></span>
                                    </div>

                                    <div x-show="form.bonus_credits > 0" class="flex justify-between text-sm">
                                        <span class="text-green-400">Crédits bonus</span>
                                        <span class="text-green-400" x-text="form.bonus_credits ? '+' + parseInt(form.bonus_credits).toLocaleString() : '+0'"></span>
                                    </div>

                                    <div class="flex justify-between font-semibold border-t border-white/20 pt-2">
                                        <span class="text-white">Total</span>
                                        <span class="text-white" x-text="totalCredits.toLocaleString()"></span>
                                    </div>
                                </div>

                                <div class="text-xs text-white/60 mb-3">
                                    <span x-text="creditValue.toFixed(3)"></span>€ par crédit
                                </div>

                                <button type="button" class="w-full py-2 bg-blue-500 text-white rounded-lg">
                                    Acheter maintenant
                                </button>
                            </div>
                        </div>

                        <!-- Calculs automatiques -->
                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                            <h3 class="text-lg font-semibold text-white mb-4">Calculs</h3>

                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-white/70">Prix en centimes</span>
                                    <span class="text-white font-mono" x-text="priceCents"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-white/70">Total crédits</span>
                                    <span class="text-white font-mono" x-text="totalCredits.toLocaleString()"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-white/70">Valeur par crédit</span>
                                    <span class="text-white font-mono" x-text="creditValue.toFixed(3) + '€'"></span>
                                </div>

                                <div class="flex justify-between border-t border-white/20 pt-2">
                                    <span class="text-white/70">Rentabilité</span>
                                    <span class="text-white font-mono"
                                          :class="profitability >= 0.3 ? 'text-green-400' : profitability >= 0.1 ? 'text-yellow-400' : 'text-red-400'"
                                          x-text="(profitability * 100).toFixed(1) + '%'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="space-y-3">
                            <button type="submit"
                                    class="w-full py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition-all">
                                Créer le package
                            </button>

                            <a href="{{ route('admin.credits.packages.index') }}"
                               class="block w-full py-3 bg-white/10 hover:bg-white/20 text-white font-medium text-center rounded-xl transition-colors">
                                Annuler
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function packageForm() {
    return {
        form: {
            name: '',
            credits_amount: 0,
            bonus_credits: 0,
            price_euros: 0
        },

        get totalCredits() {
            return parseInt(this.form.credits_amount || 0) + parseInt(this.form.bonus_credits || 0);
        },

        get priceCents() {
            return Math.round((parseFloat(this.form.price_euros || 0)) * 100);
        },

        get creditValue() {
            if (this.totalCredits === 0) return 0;
            return (parseFloat(this.form.price_euros || 0)) / this.totalCredits;
        },

        get profitability() {
            // Simulation: coût de base estimé à 0.002€ par crédit
            const baseCost = this.totalCredits * 0.002;
            const revenue = parseFloat(this.form.price_euros || 0);
            if (revenue === 0) return 0;
            return (revenue - baseCost) / revenue;
        },

        updateCalculations() {
            // Force reactivity update
            this.$nextTick(() => {
                // Updates will be automatic due to getters
            });
        }
    }
}
</script>
@endpush
