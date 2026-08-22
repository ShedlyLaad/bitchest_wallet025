<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CryptoPriceUpdateService;
use App\Services\NotificationService;

/**
 * Commande unique de mise à jour des prix crypto depuis Coinbase API.
 * Source de vérité unique : c'est un simple adaptateur CLI au-dessus de
 * CryptoPriceUpdateService, la même logique que celle utilisée par
 * l'API Admin (preview + approve). Déclenchée manuellement
 * (update-crypto-prices.bat) ou automatiquement (Laravel Scheduler, 24h).
 *
 * La protection contre les exécutions concurrentes (manuelle + automatique +
 * Admin) est gérée à l'intérieur du service via un verrou cache : elle
 * s'applique donc uniformément quelle que soit l'origine de l'appel.
 */
class UpdateCryptoPricesCommand extends Command
{
    protected $signature = 'crypto:update-prices';

    protected $description = 'Met à jour les prix crypto depuis Coinbase API et les stocke dans Redis + DB';

    public function handle(CryptoPriceUpdateService $service, NotificationService $notificationService): int
    {
        $this->info('🚀 Début de la mise à jour des prix crypto...');

        $result = $service->apply();

        if ($result['status'] === 'busy') {
            $this->comment('Une mise à jour est déjà en cours (verrou actif), exécution ignorée.');
            return 0;
        }

        foreach ($result['lines'] as $line) {
            $this->line("  {$line}");
        }

        $this->info("✅ Mise à jour terminée: {$result['updated']}/{$result['total']} cryptos");
        if ($result['failed'] > 0) {
            $this->warn("⚠️  {$result['failed']} cryptos en erreur");
        }

        if ($result['updated'] > 0) {
            $notificationService->notifyAdminsCryptoPricesUpdated($result['updated']);
        }

        return 0;
    }
}
