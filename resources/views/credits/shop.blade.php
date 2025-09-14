{{-- resources/views/credits/shop.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Boutique de Crédits - TelescopeApp')
@section('page-title', 'Boutique de Crédits')

@section('content')
<div class="p-6 lg:p-8" x-data="creditShop()" x-init="init()">

    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">🌟 Boutique de Crédits</h1>
                <p class="text-white/70 text-lg">Rechargez votre solde pour explorer l'univers sans limites</p>
            </div>

            @auth
            <div class="glass-effect rounded-xl p-4 min-w-[200px]">
                <div class="text-center">
                    <div class="text-2xl font-bold text-white mb-1">{{ auth()->user()->credits_balance }}</div>
                    <div class="text-white/60 text-sm">Crédits disponibles</div>
                </div>
            </div>
            @endauth
        </div>
    </div>

    <!-- Promotions Banner -->
    @if($activePromotions->count() > 0)
    <div class="mb-8">
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 border border-purple-500/30 rounded-xl p-6">
            <h3 class="text-xl font-semibold text-white mb-4">🎉 Offres Spéciales Actives</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($activePromotions as $promotion)
                <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-mono text-white font-bold">{{ $promotion->code }}</span>
                        <span class="text-xs bg-green-500/20 text-green-400 px-2 py-1 rounded-full">
                            @if($promotion->type === 'percentage')
                                -{{ $promotion->value }}%
                            @elseif($promotion->type === 'bonus_credits')
                                +{{ $promotion->value }} crédits
                            @else
                                -{{ $promotion->value/100 }}€
                            @endif
                        </span>
                    </div>
                    <p class="text-white/80 text-sm">{{ $promotion->description }}</p>
                    @if($promotion->expires_at)
                    <p class="text-white/60 text-xs mt-2">Expire le {{ $promotion->expires_at->format('d/m/Y') }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Featured Packages -->
    @if($featuredPackages->count() > 0)
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-white mb-6">⭐ Packages Recommandés</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredPackages as $package)
            <div class="dashboard-card group relative overflow-hidden transition-all duration-300 hover:scale-105">
                <div class="absolute top-4 right-4 bg-gradient-to-r from-yellow-400 to-orange-500 text-black px-3 py-1 rounded-full text-xs font-bold">
                    POPULAIRE
                </div>

                <div class="p-6">
                    <h3 class="text-xl font-bold text-white mb-2">{{ $package->name }}</h3>
                    <p class="text-white/70 text-sm mb-4">{{ $package->description }}</p>

                    <div class="text-center mb-6">
                        <div class="text-3xl font-bold text-white mb-1">{{ number_format($package->price_euros, 2) }}€</div>
                        <div class="text-white/60 text-sm">{{ number_format($package->total_credits) }} crédits</div>
                        @if($package->bonus_credits > 0)
                        <div class="text-green-400 text-xs mt-1">+{{ $package->bonus_credits }} crédits bonus</div>
                        @endif
                    </div>

                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-white/60">Valeur par crédit</span>
                            <span class="text-white">{{ number_format($package->credit_value, 3) }}€</span>
                        </div>
                        @if($package->savings_percentage > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-white/60">Économies</span>
                            <span class="text-green-400">{{ $package->savings_percentage }}%</span>
                        </div>
                        @endif
                    </div>

                    <button @click="selectPackage({{ $package->toJson() }})"
                            class="w-full py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg font-semibold hover:scale-105 transition-transform">
                        Acheter Maintenant
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- All Packages -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-white mb-6">Tous les Packages</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($packages as $package)
            <div class="dashboard-card group relative transition-all duration-300 hover:scale-105 {{ $package->is_featured ? 'ring-2 ring-yellow-500/50' : '' }}">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-white mb-2">{{ $package->name }}</h3>
                    <p class="text-white/70 text-sm mb-4 min-h-[40px]">{{ Str::limit($package->description, 60) }}</p>

                    <div class="text-center mb-4">
                        <div class="text-2xl font-bold text-white">{{ number_format($package->price_euros, 2) }}€</div>
                        <div class="text-white/60 text-sm">{{ number_format($package->credits_amount) }} crédits</div>
                        @if($package->bonus_credits > 0)
                        <div class="text-green-400 text-xs">+{{ $package->bonus_credits }} bonus</div>
                        @endif
                    </div>

                    <button @click="selectPackage({{ $package->toJson() }})"
                            class="w-full py-2 bg-white/10 text-white rounded-lg font-medium hover:bg-white/20 transition-colors">
                        Sélectionner
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- User Recommendations -->
    @auth
    @if($recommendations)
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-white mb-6">Recommandations Personnalisées</h2>
        <div class="space-y-6">
            @foreach($recommendations as $recommendation)
            <div class="bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-500/20 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-2">{{ $recommendation['reason'] }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($recommendation['packages']->take(3) as $package)
                    <div class="bg-white/5 rounded-lg p-4">
                        <div class="text-white font-medium">{{ $package->name }}</div>
                        <div class="text-white/60 text-sm">{{ $package->total_credits }} crédits</div>
                        <div class="text-white text-lg font-bold">{{ number_format($package->price_euros, 2) }}€</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endauth
</div>

<!-- Purchase Modal -->
<div x-show="showPurchaseModal"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">

    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" @click="closePurchaseModal()"></div>

        <div class="inline-block align-bottom bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white">Achat de Crédits</h3>
                    <button @click="closePurchaseModal()" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <template x-if="selectedPackage">
                    <div>
                        <!-- Package Details -->
                        <div class="bg-white/5 rounded-lg p-4 mb-6">
                            <h4 class="text-white font-semibold mb-2" x-text="selectedPackage.name"></h4>
                            <p class="text-white/70 text-sm mb-3" x-text="selectedPackage.description"></p>

                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-white/60">Crédits de base:</span>
                                    <span class="text-white font-medium ml-2" x-text="selectedPackage.credits_amount"></span>
                                </div>
                                <div>
                                    <span class="text-white/60">Crédits bonus:</span>
                                    <span class="text-green-400 font-medium ml-2" x-text="selectedPackage.bonus_credits"></span>
                                </div>
                                <div>
                                    <span class="text-white/60">Prix original:</span>
                                    <span class="text-white font-medium ml-2" x-text="formatPrice(selectedPackage.price_cents)"></span>
                                </div>
                                <div>
                                    <span class="text-white/60">Total crédits:</span>
                                    <span class="text-white font-bold ml-2" x-text="selectedPackage.total_credits"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Promotion Code -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-white mb-2">Code Promo (optionnel)</label>
                            <div class="flex gap-2">
                                <input type="text"
                                       x-model="promotionCode"
                                       @input="validatePromotion()"
                                       class="flex-1 bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Entrez votre code promo">
                                <button @click="validatePromotion()"
                                        :disabled="!promotionCode || promotionLoading"
                                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50">
                                    <span x-show="!promotionLoading">Valider</span>
                                    <span x-show="promotionLoading">...</span>
                                </button>
                            </div>

                            <!-- Promotion Feedback -->
                            <div x-show="promotionMessage"
                                 :class="promotionValid ? 'text-green-400' : 'text-red-400'"
                                 class="text-sm mt-2"
                                 x-text="promotionMessage"></div>
                        </div>

                        <!-- Final Pricing -->
                        <div class="bg-white/5 rounded-lg p-4 mb-6">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-white/60">Prix de base:</span>
                                    <span class="text-white" x-text="formatPrice(selectedPackage.price_cents)"></span>
                                </div>

                                <div x-show="promotionValid && promotionDiscount.discount_amount > 0" class="flex justify-between">
                                    <span class="text-white/60">Réduction:</span>
                                    <span class="text-green-400" x-text="'-' + formatPrice(promotionDiscount.discount_amount)"></span>
                                </div>

                                <div x-show="promotionValid && promotionDiscount.bonus_credits > 0" class="flex justify-between">
                                    <span class="text-white/60">Crédits bonus promo:</span>
                                    <span class="text-green-400" x-text="'+' + promotionDiscount.bonus_credits"></span>
                                </div>

                                <hr class="border-white/20">

                                <div class="flex justify-between text-lg font-bold">
                                    <span class="text-white">Total à payer:</span>
                                    <span class="text-white" x-text="formatPrice(finalPrice)"></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-white">Total crédits reçus:</span>
                                    <span class="text-green-400 font-bold" x-text="totalCredits"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <div class="mb-6">
                            <div id="payment-element" class="mb-4">
                                <!-- Stripe Payment Element will be mounted here -->
                            </div>
                            <div id="payment-errors" class="text-red-400 text-sm"></div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button @click="processPurchase()"
                        :disabled="purchasing || !paymentElementReady"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                    <span x-show="!purchasing">Finaliser l'Achat</span>
                    <span x-show="purchasing">Traitement...</span>
                </button>
                <button @click="closePurchaseModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Annuler
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
function creditShop() {
    return {
        showPurchaseModal: false,
        selectedPackage: null,
        promotionCode: '',
        promotionValid: false,
        promotionMessage: '',
        promotionLoading: false,
        promotionDiscount: null,
        purchasing: false,
        stripe: null,
        elements: null,
        paymentElement: null,
        paymentElementReady: false,

        init() {
            // Initialize Stripe
            this.stripe = Stripe('{{ config("cashier.key") }}');
            console.log('💳 Credit Shop initialized');
        },

        selectPackage(packageData) {
            this.selectedPackage = packageData;
            this.showPurchaseModal = true;
            this.resetPromotion();

            // Initialize Stripe Elements when modal opens
            this.$nextTick(() => {
                this.initializePaymentElement();
            });
        },

        closePurchaseModal() {
            this.showPurchaseModal = false;
            this.selectedPackage = null;
            this.resetPromotion();
            this.paymentElementReady = false;

            if (this.elements) {
                this.elements = null;
                this.paymentElement = null;
            }
        },

        resetPromotion() {
            this.promotionCode = '';
            this.promotionValid = false;
            this.promotionMessage = '';
            this.promotionDiscount = null;
        },

        async initializePaymentElement() {
            if (!this.selectedPackage) return;

            try {
                // Create client secret for the payment
                const response = await fetch('/api/create-payment-intent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        package_id: this.selectedPackage.id,
                        promotion_code: this.promotionCode
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message);
                }

                // Initialize Elements
                this.elements = this.stripe.elements({
                    clientSecret: data.client_secret,
                    appearance: {
                        theme: 'night',
                        variables: {
                            colorPrimary: '#6366f1',
                            colorBackground: '#1f2937',
                            colorText: '#ffffff',
                            colorDanger: '#ef4444',
                            fontFamily: 'Inter, system-ui, sans-serif',
                            spacingUnit: '4px',
                            borderRadius: '8px'
                        }
                    }
                });

                // Create Payment Element
                this.paymentElement = this.elements.create('payment');
                this.paymentElement.mount('#payment-element');

                this.paymentElement.on('ready', () => {
                    this.paymentElementReady = true;
                });

                this.paymentElement.on('change', (event) => {
                    const displayError = document.getElementById('payment-errors');
                    if (event.error) {
                        displayError.textContent = event.error.message;
                    } else {
                        displayError.textContent = '';
                    }
                });

            } catch (error) {
                console.error('Payment initialization error:', error);
                window.showNotification('Erreur', 'Impossible d\'initialiser le paiement', 'error');
            }
        },

        async validatePromotion() {
            if (!this.promotionCode.trim()) {
                this.resetPromotion();
                return;
            }

            this.promotionLoading = true;

            try {
                const response = await fetch('/credits/validate-promotion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        code: this.promotionCode,
                        package_id: this.selectedPackage.id
                    })
                });

                const data = await response.json();

                this.promotionValid = data.valid;
                this.promotionMessage = data.message;

                if (data.valid) {
                    this.promotionDiscount = data.discount;
                } else {
                    this.promotionDiscount = null;
                }

            } catch (error) {
                console.error('Promotion validation error:', error);
                this.promotionMessage = 'Erreur lors de la validation';
                this.promotionValid = false;
            } finally {
                this.promotionLoading = false;
            }
        },

        async processPurchase() {
            if (!this.paymentElementReady || this.purchasing) return;

            this.purchasing = true;

            try {
                const { error } = await this.stripe.confirmPayment({
                    elements: this.elements,
                    confirmParams: {
                        return_url: window.location.origin + '/credits/success',
                    },
                    redirect: 'if_required'
                });

                if (error) {
                    console.error('Payment error:', error);
                    window.showNotification('Erreur de Paiement', error.message, 'error');
                } else {
                    // Payment succeeded
                    window.showNotification('Succès', 'Paiement réussi ! Vos crédits ont été ajoutés.', 'success');
                    this.closePurchaseModal();

                    // Refresh page to update balance
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }

            } catch (error) {
                console.error('Purchase error:', error);
                window.showNotification('Erreur', 'Une erreur est survenue lors du paiement', 'error');
            } finally {
                this.purchasing = false;
            }
        },

        get finalPrice() {
            if (!this.selectedPackage) return 0;

            let price = this.selectedPackage.price_cents;

            if (this.promotionValid && this.promotionDiscount) {
                price -= this.promotionDiscount.discount_amount || 0;
            }

            return Math.max(0, price);
        },

        get totalCredits() {
            if (!this.selectedPackage) return 0;

            let credits = this.selectedPackage.credits_amount + this.selectedPackage.bonus_credits;

            if (this.promotionValid && this.promotionDiscount) {
                credits += this.promotionDiscount.bonus_credits || 0;
            }

            return credits;
        },

        formatPrice(cents) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            }).format(cents / 100);
        }
    }
}
</script>
@endpush
