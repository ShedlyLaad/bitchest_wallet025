<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Portfolio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetPortfolioData extends Command
{
    protected $signature = 'portfolio:reset {--balance=500 : Initial balance for users}';
    protected $description = 'Reset all portfolio data (transactions, portfolios) while keeping users. Sets all user balances to a default value.';

    public function handle()
    {
        $defaultBalance = (float) $this->option('balance');
        
        $this->info('🔄 Resetting portfolio data...');
        
        // 1. Supprimer toutes les transactions (delete au lieu de truncate pour être compatible avec les transactions)
        $transactionCount = Transaction::count();
        Transaction::query()->delete();
        $this->info("✅ Deleted {$transactionCount} transactions");
        
        // 2. Réinitialiser tous les portfolios
        $portfolioCount = Portfolio::count();
        Portfolio::query()->update(['total_crypto_value' => 0]);
        $this->info("✅ Reset {$portfolioCount} portfolios");
        
        // 3. Réinitialiser toutes les balances utilisateur à la valeur par défaut
        $userCount = User::where('role', 'client')->count();
        User::where('role', 'client')->update(['euro_balance' => $defaultBalance]);
        $this->info("✅ Reset {$userCount} client balances to €{$defaultBalance}");
        
        // 4. Garder l'admin avec une balance de 0 (ou ne pas toucher)
        $adminCount = User::where('role', 'admin')->count();
        $this->info("ℹ️  {$adminCount} admin(s) kept unchanged");
        
        $this->info('✨ Portfolio data reset completed successfully!');
        $this->info("All client users now have €{$defaultBalance} balance.");
        $this->info('All transactions and portfolio values have been cleared.');
        
        return 0;
    }
}
