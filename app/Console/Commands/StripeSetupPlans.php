<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;
use App\Models\Subscription;

class StripeSetupPlans extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'stripe:setup-plans
                            {--force : Force recreation of existing products}';

    /**
     * The console command description.
     */
    protected $description = 'Create Stripe products and prices for subscription plans';

    /**
     * Plans configuration
     */
    protected $plans = [
        'stardust' => [
            'name' => 'Stardust',
            'description' => 'Plan débutant - 20 crédits/mois - Priorité Eco',
            'price' => 2900, // en centimes
            'credits' => 20,
            'emoji' => '🌟',
            'features' => [
                'Priority 0-1 (Very Low/Low)',
                'Mode One Shot uniquement',
                'Sans garantie netteté',
                '20 crédits/mois',
            ],
        ],
        'nebula' => [
            'name' => 'Nebula',
            'description' => 'Plan amateur confirmé - 60 crédits/mois - Priorité Standard',
            'price' => 5900,
            'credits' => 60,
            'emoji' => '🌌',
            'features' => [
                'Priority 0-2 (jusqu\'à Normal)',
                'Option Nuit Noire (x2 crédits)',
                'Garantie netteté standard (4.0px)',
                'Dashboard temps réel',
                'Mode Repeat disponible',
                '60 crédits/mois',
            ],
        ],
        'quasar' => [
            'name' => 'Quasar',
            'description' => 'Plan Expert/VIP - 150 crédits/mois - Priorité VIP',
            'price' => 11900,
            'credits' => 150,
            'emoji' => '⚡',
            'features' => [
                'Priority 0-4 (accès First)',
                'Option Nuit Noire incluse',
                'Garantie netteté ajustable (1.5-4.0px)',
                'Dashboard temps réel',
                'Projets multi-nuits',
                'Gestion avancée des Sets',
                '150 crédits/mois',
            ],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Vérifier la configuration Stripe
        $stripeKey = config('cashier.secret');

        if (!$stripeKey) {
            $this->error('❌ Stripe secret key not configured in .env');
            $this->info('Please add STRIPE_SECRET to your .env file');
            return 1;
        }

        Stripe::setApiKey($stripeKey);

        $this->info('🚀 Starting Stripe plans setup...');
        $this->newLine();

        $priceIds = [];

        foreach ($this->plans as $planKey => $planData) {
            $this->info("📦 Processing plan: {$planData['emoji']} {$planData['name']}");

            try {
                // Créer ou récupérer le produit
                $product = $this->createOrGetProduct($planKey, $planData);
                $this->line("   ✓ Product: {$product->id}");

                // Créer le price
                $price = $this->createPrice($product, $planData);
                $this->line("   ✓ Price: {$price->id}");

                // Stocker le Price ID
                $priceIds[strtoupper($planKey)] = $price->id;

                $this->newLine();

            } catch (\Exception $e) {
                $this->error("   ✗ Error: {$e->getMessage()}");
                $this->newLine();
                continue;
            }
        }

        // Mettre à jour le fichier .env
        if (!empty($priceIds)) {
            $this->info('💾 Updating .env file with Price IDs...');
            $this->updateEnvFile($priceIds);
            $this->info('✅ .env file updated successfully!');
            $this->newLine();
        }

        // Afficher le résumé
        $this->displaySummary($priceIds);

        // Clear config cache
        $this->call('config:clear');

        $this->newLine();
        $this->info('🎉 Stripe plans setup completed!');
        $this->info('💡 You can now use these plans in your application');

        return 0;
    }

    /**
     * Create or get existing product
     */
    protected function createOrGetProduct(string $planKey, array $planData): Product
    {
        $metadataKey = "stellar_plan_{$planKey}";

        // Chercher un produit existant
        $existingProducts = Product::all([
            'limit' => 100,
        ]);

        foreach ($existingProducts->data as $product) {
            if (isset($product->metadata->stellar_plan) &&
                $product->metadata->stellar_plan === $planKey) {

                if ($this->option('force')) {
                    $this->line("   ⟳ Updating existing product...");
                    return Product::update($product->id, [
                        'name' => $planData['name'],
                        'description' => $planData['description'],
                        'metadata' => [
                            'stellar_plan' => $planKey,
                            'credits_per_month' => $planData['credits'],
                        ],
                    ]);
                }

                $this->line("   ⟳ Using existing product...");
                return $product;
            }
        }

        // Créer un nouveau produit
        $this->line("   + Creating new product...");
        return Product::create([
            'name' => $planData['name'],
            'description' => $planData['description'],
            'metadata' => [
                'stellar_plan' => $planKey,
                'credits_per_month' => $planData['credits'],
            ],
        ]);
    }

    /**
     * Create price for product
     */
    protected function createPrice(Product $product, array $planData): Price
    {
        // Chercher un price actif existant
        $existingPrices = Price::all([
            'product' => $product->id,
            'active' => true,
            'limit' => 10,
        ]);

        foreach ($existingPrices->data as $price) {
            if ($price->unit_amount === $planData['price'] &&
                $price->recurring->interval === 'month') {

                if (!$this->option('force')) {
                    $this->line("   ⟳ Using existing price...");
                    return $price;
                }
            }
        }

        // Créer un nouveau price
        $this->line("   + Creating new price...");
        return Price::create([
            'product' => $product->id,
            'unit_amount' => $planData['price'],
            'currency' => 'eur',
            'recurring' => [
                'interval' => 'month',
            ],
            'metadata' => [
                'stellar_plan' => $product->metadata->stellar_plan,
                'credits_per_month' => $planData['credits'],
            ],
        ]);
    }

    /**
     * Update .env file with price IDs
     */
    protected function updateEnvFile(array $priceIds): void
    {
        $envFile = base_path('.env');

        if (!file_exists($envFile)) {
            $this->error('.env file not found');
            return;
        }

        $envContent = file_get_contents($envFile);

        foreach ($priceIds as $plan => $priceId) {
            $key = "STRIPE_PRICE_{$plan}";

            // Si la clé existe, la remplacer
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$priceId}",
                    $envContent
                );
            } else {
                // Sinon, l'ajouter à la fin de la section Stripe
                // Chercher la section Stripe
                if (preg_match("/STRIPE_SECRET=.*/", $envContent)) {
                    $envContent = preg_replace(
                        "/(STRIPE_SECRET=.*)/",
                        "$1\n{$key}={$priceId}",
                        $envContent
                    );
                } else {
                    // Ajouter à la fin
                    $envContent .= "\n{$key}={$priceId}";
                }
            }
        }

        file_put_contents($envFile, $envContent);
    }

    /**
     * Display summary table
     */
    protected function displaySummary(array $priceIds): void
    {
        $this->newLine();
        $this->info('📊 Summary of created plans:');
        $this->newLine();

        $headers = ['Plan', 'Name', 'Price', 'Credits', 'Price ID'];
        $rows = [];

        foreach ($this->plans as $planKey => $planData) {
            $priceIdKey = strtoupper($planKey);
            $rows[] = [
                $planData['emoji'] . ' ' . ucfirst($planKey),
                $planData['name'],
                number_format($planData['price'] / 100, 2) . '€',
                $planData['credits'],
                $priceIds[$priceIdKey] ?? 'N/A',
            ];
        }

        $this->table($headers, $rows);
    }
}
