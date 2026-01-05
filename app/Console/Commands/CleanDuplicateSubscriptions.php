<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateSubscriptions extends Command
{
    protected $signature = 'subscriptions:clean-duplicates
                          {--dry-run : Afficher les doublons sans les supprimer}';

    protected $description = 'Nettoyer les abonnements dupliqués (même stripe_id)';

    public function handle()
    {
        $this->info('🔍 Recherche des abonnements dupliqués...');

        // Trouver les stripe_id en double
        $duplicates = DB::table('subscriptions')
            ->select('stripe_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('stripe_id')
            ->where('stripe_id', '!=', '')
            ->groupBy('stripe_id')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('✅ Aucun doublon trouvé!');
            return 0;
        }

        $this->warn("⚠️  {$duplicates->count()} stripe_id(s) en double trouvé(s)\n");

        $deleted = 0;
        $kept = 0;

        foreach ($duplicates as $duplicate) {
            $stripeId = $duplicate->stripe_id;
            $this->line("Traitement de stripe_id: {$stripeId}");

            // Récupérer tous les abonnements avec ce stripe_id
            $subscriptions = Subscription::where('stripe_id', $stripeId)
                ->orderBy('id')
                ->get();

            $this->line("  Trouvé {$subscriptions->count()} abonnements");

            // Garder le premier, supprimer les autres
            $first = $subscriptions->first();
            $toDelete = $subscriptions->skip(1);

            $this->info("  ✓ Garder: ID #{$first->id} (User: {$first->user_id})");

            foreach ($toDelete as $sub) {
                if ($this->option('dry-run')) {
                    $this->warn("  ⚠️  [DRY RUN] Supprimerait: ID #{$sub->id} (User: {$sub->user_id})");
                } else {
                    $sub->delete();
                    $this->error("  ✗ Supprimé: ID #{$sub->id} (User: {$sub->user_id})");
                    $deleted++;
                }
            }

            $kept++;
            $this->newLine();
        }

        if ($this->option('dry-run')) {
            $this->warn("\n🔍 MODE DRY-RUN: Aucune suppression effectuée");
            $this->info("Exécutez sans --dry-run pour supprimer les doublons");
        } else {
            $this->info("\n✅ Nettoyage terminé!");
            $this->info("   • {$kept} abonnement(s) conservé(s)");
            $this->info("   • {$deleted} doublon(s) supprimé(s)");
        }

        return 0;
    }
}
