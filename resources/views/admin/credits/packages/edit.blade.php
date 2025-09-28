{{-- resources/views/admin/credits/packages/edit.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Modifier le Package - Admin')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900/50 to-slate-900 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(120,119,198,0.1),transparent_50%)]"></div>

    <div class="relative z-10 max-w-4xl mx-auto px-6 py-8">

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Modifier le Package</h1>
                <p class="text-white/60 mt-2">Modifiez les détails du package "{{ $package->name }}"</p>
            </div>
            <a href="{{ route('admin.credits.packages.index') }}"
               class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors">
                ← Retour à la liste
            </a>
        </div>

        <!-- Messages d'erreur globaux -->
        @if ($errors->any())
        <div class="bg-red-500/20 border border-red-500/30 rounded-xl p-4 mb-6">
            <div class="flex items-center gap-3 mb-3">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <h3 class="text-red-400 font-semibold">Erreurs de validation</h3>
            </div>
            <ul class="list-disc list-inside text-red-300 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Formulaire -->
        <form action="{{ route('admin.credits.packages.update', $package) }}" method="POST" class="space-y-8" x-data="packageForm()">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Formulaire principal -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Informations de base -->
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Informations de base
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-white mb-2">Nom du package *</label>
                                <input type="text"
                                       name="name"
                                       value="{{ old('name', $package->name) }}"
                                       required
                                       x-model="formData.name"
                                       class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                       placeholder="Pack Explorer">
                                @error('name')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-white mb-2">Description</label>
                                <textarea name="description"
                                          rows="3"
                                          x-model="formData.description"
                                          class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                                          placeholder="Description du package pour les utilisateurs">{{ old('description', $package->description) }}</textarea>
                                @error('description')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-white mb-2">Ordre de tri</label>
                                <input type="number"
                                       name="sort_order"
                                       value="{{ old('sort_order', $package->sort_order) }}"
                                       min="0"
                                       max="1000"
                                       class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                       placeholder="0">
                                <p class="text-white/60 text-xs mt-1">Plus le nombre est faible, plus le package apparaît en premier</p>
                                @error('sort_order')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Tarification -->
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Tarification et crédits
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-white mb-2">Prix en euros *</label>
                                <div class="relative">
                                    <input type="number"
                                           name="price_euros"
                                           value="{{ old('price_euros', number_format($package->price_euros, 2, '.', '')) }}"
                                           step="0.01"
                                           min="0.01"
                                           max="5000"
                                           required
                                           x-model="formData.price_euros"
                                           @input="updateCalculations()"
                                           class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 pr-12 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                           placeholder="9.99">
                                    <span class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white/60">€</span>
                                </div>
                                @error('price_euros')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-white mb-2">Nombre de crédits *</label>
                                <input type="number"
                                       name="credits_amount"
                                       value="{{ old('credits_amount', $package->credits_amount) }}"
                                       min="1"
                                       max="50000"
                                       required
                                       x-model="formData.credits_amount"
                                       @input="updateCalculations()"
                                       class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                       placeholder="1000">
                                @error('credits_amount')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-semibold text-white mb-2">Crédits bonus</label>
                                <input type="number"
                                       name="bonus_credits"
                                       value="{{ old('bonus_credits', $package->bonus_credits) }}"
                                       min="0"
                                       max="10000"
                                       x-model="formData.bonus_credits"
                                       @input="updateCalculations()"
                                       class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                       placeholder="0">
                                @error('bonus_credits')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-white mb-2">Réduction (%)</label>
                                <input type="number"
                                       name="discount_percentage"
                                       value="{{ old('discount_percentage', $package->discount_percentage) }}"
                                       min="0"
                                       max="100"
                                       class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                       placeholder="0">
                                @error('discount_percentage')
                                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Options du package
                        </h3>

                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           name="is_active"
                                           value="1"
                                           {{ old('is_active', $package->is_active) ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                                </label>
                                <span class="text-white font-medium">Package actif</span>
                                <span class="text-white/60 text-sm">Visible dans la boutique</span>
                            </div>

                            <div class="flex items-center gap-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           name="is_featured"
                                           value="1"
                                           {{ old('is_featured', $package->is_featured) ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
                                </label>
                                <span class="text-white font-medium">Package populaire</span>
                                <span class="text-white/60 text-sm">Mis en avant avec badge spécial</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-4 pt-6">
                        <button type="submit"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all transform hover:scale-105">
                            Mettre à jour le package
                        </button>
                        <a href="{{ route('admin.credits.packages.index') }}"
                           class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                            Annuler
                        </a>
                    </div>
                </div>

                <!-- Aperçu en temps réel -->
                <div class="lg:col-span-1">
                    <div class="sticky top-8">
                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                            <h3 class="text-lg font-bold text-white mb-4">Aperçu du package</h3>

                            <!-- Carte preview -->
                            <div class="bg-gradient-to-br from-blue-500/20 to-purple-600/20 border border-blue-500/30 rounded-xl p-4 mb-4" :class="formData.is_featured ? 'ring-2 ring-yellow-500/50' : ''">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-bold text-white" x-text="formData.name || 'Nom du package'"></h4>
                                    <span x-show="formData.is_featured" class="bg-yellow-500 text-black text-xs px-2 py-1 rounded-full font-semibold">POPULAIRE</span>
                                </div>
                                <p class="text-white/70 text-sm mb-3 min-h-[40px]" x-text="formData.description || 'Description du package'"></p>

                                <div class="text-center mb-3">
                                    <div class="text-xl font-bold text-white" x-text="formatPrice(formData.price_euros)"></div>
                                    <div class="text-white/60 text-sm" x-text="formatCredits(formData.credits_amount) + ' crédits'"></div>
                                    <div x-show="formData.bonus_credits > 0" class="text-green-400 text-xs" x-text="'+' + formatCredits(formData.bonus_credits) + ' bonus'"></div>
                                </div>

                                <button type="button" class="w-full py-2 bg-white/10 text-white rounded-lg font-medium cursor-default">
                                    Sélectionner
                                </button>
                            </div>

                            <!-- Statistiques calculées -->
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-white/60">Total crédits:</span>
                                    <span class="text-white font-semibold" x-text="formatCredits(totalCredits)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-white/60">Prix par crédit:</span>
                                    <span class="text-white font-semibold" x-text="creditValue + '€'"></span>
                                </div>
                                <div x-show="formData.bonus_credits > 0" class="flex justify-between">
                                    <span class="text-white/60">Valeur bonus:</span>
                                    <span class="text-green-400 font-semibold" x-text="bonusValue + '€'"></span>
                                </div>
                            </div>
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
        formData: {
            name: '{{ $package->name }}',
            description: '{{ $package->description }}',
            price_euros: {{ number_format($package->price_euros, 2, '.', '') }},
            credits_amount: {{ $package->credits_amount }},
            bonus_credits: {{ $package->bonus_credits ?? 0 }},
            is_featured: {{ $package->is_featured ? 'true' : 'false' }}
        },

        get totalCredits() {
            return (parseInt(this.formData.credits_amount) || 0) + (parseInt(this.formData.bonus_credits) || 0);
        },

        get creditValue() {
            const price = parseFloat(this.formData.price_euros) || 0;
            const credits = parseInt(this.formData.credits_amount) || 1;
            return (price / credits).toFixed(4);
        },

        get bonusValue() {
            const pricePerCredit = parseFloat(this.creditValue);
            const bonusCredits = parseInt(this.formData.bonus_credits) || 0;
            return (pricePerCredit * bonusCredits).toFixed(2);
        },

        formatPrice(price) {
            return parseFloat(price || 0).toFixed(2) + '€';
        },

        formatCredits(credits) {
            return parseInt(credits || 0).toLocaleString();
        },

        updateCalculations() {
            // Force reactivity update
            this.$nextTick(() => {
                // Update computed values
            });
        }
    }
}
</script>
@endpush
