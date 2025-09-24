{{-- resources/views/credits/shop.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Boutique de Crédits - AstroSphere')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900/50 to-slate-900 relative overflow-hidden">
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
                    <div class="text-xl font-bold text-white" id="current-balance">{{ number_format(auth()->user()->credits_balance) }} crédits</div>
                </div>
            </div>
        </div>

        <!-- Promotions actives -->
        @if($promotions->count() > 0)
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-1V6a2 2 0 00-2-2v10.5a.5.5 0 01-.5.5h-1a.5.5 0 01-.5-.5V8z"/>
                </svg>
                Offres Spéciales
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($promotions as $promotion)
                <div class="bg-gradient-to-br from-red-500/20 to-orange-500/20 border border-red-500/30 rounded-2xl p-6 relative overflow-hidden">
                    <div class="absolute top-4 right-4 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                        {{ $promotion->formatted_value }}
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">{{ $promotion->name }}</h3>
                    <p class="text-white/70 text-sm mb-4">{{ $promotion->description }}</p>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="bg-red-500/20 text-red-300 px-2 py-1 rounded-full font-medium">
                            Code: {{ $promotion->code }}
                        </span>
                        @if($promotion->expires_at)
                        <span class="text-white/60">
                            Expire le {{ $promotion->expires_at->format('d/m/Y') }}
                        </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Packages de crédits -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-white mb-8 text-center">Choisissez votre package</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" x-data="creditShop()">
                @foreach($packages as $package)
                <div class="relative group">
                    @if($package->is_featured)
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 z-10">
                        <span class="bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs font-bold px-4 py-2 rounded-full">
                            POPULAIRE
                        </span>
                    </div>
                    @endif

                    <div class="package-card relative bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6 h-full transition-all duration-300 hover:bg-white/10 hover:border-white/20 hover:scale-105 cursor-pointer {{ $package->is_featured ? 'ring-2 ring-purple-500/50' : '' }}"
                         @click="selectPackage({
                             id: {{ $package->id }},
                             name: '{{ $package->name }}',
                             description: '{{ $package->description }}',
                             credits_amount: {{ $package->credits_amount }},
                             bonus_credits: {{ $package->bonus_credits }},
                             total_credits: {{ $package->total_credits }},
                             price_cents: {{ $package->price_cents }},
                             price_euros: {{ $package->price_euros }},
                             currency: '{{ $package->currency }}'
                         })">

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

                        <!-- Économies -->
                        @if($package->savings_percentage > 0)
                        <div class="mb-4">
                            <div class="bg-green-500/20 border border-green-500/30 rounded-lg p-3 text-center">
                                <span class="text-green-400 font-semibold">
                                    Économisez {{ $package->savings_percentage }}%
                                </span>
                            </div>
                        </div>
                        @endif

                        <!-- Bouton d'achat -->
                        <button class="w-full py-3 px-6 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 hover:scale-105 group-hover:shadow-lg group-hover:shadow-purple-500/25">
                            Acheter maintenant
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Modal d'achat -->
        <div x-show="showPurchaseModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
             style="display: none;">

            <div class="bg-slate-900 border border-white/20 rounded-2xl max-w-md w-full max-h-[90vh] overflow-y-auto"
                 @click.away="closePurchaseModal()">

                <!-- Header du modal -->
                <div class="p-6 border-b border-white/10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-white" x-text="selectedPackage?.name"></h3>
                        <button @click="closePurchaseModal()" class="text-white/60 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Contenu du modal -->
                <div class="p-6">
                    <!-- Résumé du package -->
                    <div class="bg-white/5 rounded-xl p-4 mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-white/70">Crédits</span>
                            <span class="text-white font-semibold" x-text="selectedPackage ? selectedPackage.total_credits.toLocaleString() + ' crédits' : ''"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-white/70">Prix</span>
                            <span class="text-white font-bold text-lg" x-text="selectedPackage ? selectedPackage.price_euros.toFixed(2) + '€' : ''"></span>
                        </div>
                    </div>

                    <!-- Code promotionnel -->
                    <div class="mb-6">
                        <label class="block text-white font-medium mb-2">Code promotionnel (optionnel)</label>
                        <div class="flex gap-2">
                            <input type="text"
                                   x-model="promotionCode"
                                   placeholder="Entrez votre code"
                                   class="flex-1 bg-white/5 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-blue-400">
                            <button @click="validatePromotion()"
                                    :disabled="!promotionCode || promotionLoading"
                                    class="px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-xl transition-colors disabled:opacity-50">
                                <span x-show="!promotionLoading">Valider</span>
                                <span x-show="promotionLoading">...</span>
                            </button>
                        </div>

                        <!-- Message de promotion -->
                        <div x-show="promotionMessage"
                             class="mt-2 p-3 rounded-lg"
                             :class="promotionValid ? 'bg-green-500/20 border border-green-500/30 text-green-300' : 'bg-red-500/20 border border-red-500/30 text-red-300'"
                             x-text="promotionMessage">
                        </div>
                    </div>

                    <!-- Élément de paiement Stripe -->
                    <div id="payment-element" class="mb-6 p-4 bg-white/5 rounded-xl min-h-[200px] flex items-center justify-center">
                        <div x-show="!paymentElementReady" class="text-white/60">
                            Chargement du formulaire de paiement...
                        </div>
                    </div>

                    <!-- Bouton de paiement -->
                    <button @click="processPayment()"
                            :disabled="!paymentElementReady || purchasing"
                            class="w-full py-4 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold rounded-xl transition-all disabled:opacity-50">
                        <span x-show="!purchasing">Confirmer l'achat</span>
                        <span x-show="purchasing" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Traitement en cours...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Informations supplémentaires -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Activation instantanée</h3>
                <p class="text-white/60">Vos crédits sont disponibles immédiatement après le paiement</p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-green-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Paiement sécurisé</h3>
                <p class="text-white/60">Transactions protégées par Stripe avec cryptage SSL</p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-purple-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Support 24/7</h3>
                <p class="text-white/60">Une question ? Notre équipe est là pour vous aider</p>
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
            console.log('Credit Shop initialized');
        },

        async selectPackage(packageData) {
            this.selectedPackage = packageData;
            this.showPurchaseModal = true;
            this.resetPromotion();

            // Initialize Stripe Elements when modal opens
            await this.$nextTick();
            await this.initializePaymentElement();
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

        async validatePromotion() {
            if (!this.promotionCode || !this.selectedPackage) return;

            this.promotionLoading = true;

            try {
                const response = await fetch('{{ route("credits.validate-promotion") }}', {
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
                    this.promotionDiscount = data.promotion;
                    await this.updatePaymentIntent();
                }

            } catch (error) {
                this.promotionMessage = 'Erreur lors de la validation';
                this.promotionValid = false;
            } finally {
                this.promotionLoading = false;
            }
        },

        async initializePaymentElement() {
            if (!this.selectedPackage) return;

            try {
                // Create payment intent
                const response = await fetch('/api/credits/create-payment-intent', {
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
                    throw new Error(data.error);
                }

                // Initialize Stripe Elements
                this.elements = this.stripe.elements({
                    clientSecret: data.client_secret,
                    appearance: {
                        theme: 'night',
                        variables: {
                            colorPrimary: '#6366f1',
                            colorBackground: 'rgba(255, 255, 255, 0.05)',
                            colorText: '#ffffff',
                            colorDanger: '#ef4444',
                            fontFamily: 'system-ui, sans-serif',
                            borderRadius: '12px'
                        }
                    }
                });

                this.paymentElement = this.elements.create('payment');
                this.paymentElement.mount('#payment-element');

                this.paymentElement.on('ready', () => {
                    this.paymentElementReady = true;
                });

            } catch (error) {
                console.error('Payment initialization failed:', error);
                alert('Erreur lors de l\'initialisation du paiement');
            }
        },

        async updatePaymentIntent() {
            // Recreate payment intent with promotion
            await this.initializePaymentElement();
        },

        async processPayment() {
            if (!this.paymentElementReady || this.purchasing) return;

            this.purchasing = true;

            try {
                const { error, paymentIntent } = await this.stripe.confirmPayment({
                    elements: this.elements,
                    redirect: 'if_required'
                });

                if (error) {
                    throw new Error(error.message);
                }

                if (paymentIntent.status === 'succeeded') {
                    // Confirm payment on backend
                    const response = await fetch('/api/credits/confirm-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            payment_intent_id: paymentIntent.id
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Update balance display
                        document.getElementById('current-balance').textContent =
                            data.new_balance.toLocaleString() + ' crédits';

                        this.closePurchaseModal();
                        this.showSuccessNotification(data.message);

                        // Redirect to success page
                        if (data.redirect_url) {
                            setTimeout(() => window.location.href = data.redirect_url, 1500);
                        }
                    } else {
                        throw new Error(data.error || 'Erreur lors de la confirmation');
                    }
                } else {
                    throw new Error('Le paiement n\'a pas été confirmé');
                }

            } catch (error) {
                console.error('Payment failed:', error);
                alert('Erreur: ' + error.message);
            } finally {
                this.purchasing = false;
            }
        },

        showSuccessNotification(message) {
            // Create temporary notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-6 right-6 bg-green-500 text-white px-6 py-4 rounded-xl shadow-lg z-50';
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
