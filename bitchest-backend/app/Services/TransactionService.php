<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\CryptoCurrency;
use App\Models\Portfolio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service de gestion des transactions
 * 
 * Responsabilités :
 * - Traitement des transactions d'achat/vente
 * - Validation des règles métier (solde, quantité)
 * - Gestion de l'intégrité des données (transactions DB, row locking)
 * - Coordination avec PortfolioService et NotificationService
 */
class TransactionService
{
    private const MIN_QUANTITY = 0.00000001; // Quantité minimale pour une transaction
    
    public function __construct(
        private PortfolioService $portfolioService,
        private NotificationService $notificationService
    ) {}

    /**
     * Traite une transaction d'achat ou de vente
     * 
     * @param User $user Utilisateur effectuant la transaction
     * @param CryptoCurrency $crypto Cryptomonnaie concernée
     * @param float $quantity Quantité à acheter/vendre
     * @param float $price Prix unitaire de la crypto
     * @param string $type Type de transaction ('buy' ou 'sell')
     * @return Transaction Transaction créée
     * @throws \InvalidArgumentException Si les règles métier ne sont pas respectées
     */
    public function processTransaction(
        User $user,
        CryptoCurrency $crypto,
        float $quantity,
        float $price,
        string $type
    ): Transaction {
        // Validation des paramètres
        $this->validateTransactionParams($quantity, $price, $type);
        
        return DB::transaction(function () use ($user, $crypto, $quantity, $price, $type) {
            // Verrouiller la ligne utilisateur pour éviter les race conditions
            $user = User::where('id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();
            
            $amount = $quantity * $price;
            
            // Trouver ou créer le portfolio pour cette crypto
            $portfolio = $this->findOrCreatePortfolio($user->id, $crypto->id);
            
            // Valider et traiter selon le type
            if ($type === 'buy') {
                $this->processBuy($user, $amount);
            } else {
                $this->processSell($user, $portfolio, $quantity, $amount);
            }
            
            // Créer l'enregistrement de transaction
            $transaction = $this->createTransaction($portfolio, $type, $quantity, $price, $amount);
            
            // Mettre à jour le portfolio
            $this->portfolioService->updatePortfolio(
                $portfolio,
                $transaction,
                $quantity,
                $price,
                $type
            );
            
            // Vérifier et créer les notifications (profit/loss, level_up)
            $this->notificationService->checkAndCreatePortfolioNotifications($user);
            
            return $transaction;
        });
    }
    
    /**
     * Valide les paramètres d'une transaction
     */
    private function validateTransactionParams(float $quantity, float $price, string $type): void
    {
        if ($quantity < self::MIN_QUANTITY) {
            throw new \InvalidArgumentException("La quantité doit être supérieure à " . self::MIN_QUANTITY);
        }
        
        if ($price <= 0) {
            throw new \InvalidArgumentException("Le prix doit être supérieur à 0");
        }
        
        if (!in_array($type, ['buy', 'sell'])) {
            throw new \InvalidArgumentException("Le type de transaction doit être 'buy' ou 'sell'");
        }
    }
    
    /**
     * Trouve ou crée un portfolio pour un utilisateur et une crypto
     */
    private function findOrCreatePortfolio(int $userId, int $cryptoId): Portfolio
    {
        return Portfolio::firstOrCreate(
            [
                'user_id' => $userId,
                'crypto_currency_id' => $cryptoId,
            ],
            [
                'total_crypto_value' => 0,
            ]
        );
    }
    
    /**
     * Traite un achat de crypto
     */
    private function processBuy(User $user, float $amount): void
    {
        $currentBalance = (float) ($user->euro_balance ?? 0.0);
        
        if ($currentBalance < $amount) {
            throw new \InvalidArgumentException(
                "Solde insuffisant. Solde disponible : " . number_format($currentBalance, 2) . " EUR."
            );
        }
        
        $user->euro_balance = $currentBalance - $amount;
        $user->save();
    }
    
    /**
     * Traite une vente de crypto
     */
    private function processSell(User $user, Portfolio $portfolio, float $quantity, float $amount): void
    {
        // Utiliser le cache Redis pour les quantités (plus rapide)
        $totalBuyQuantity = Transaction::getCachedQuantity($portfolio->id, 'buy');
        $totalSellQuantity = Transaction::getCachedQuantity($portfolio->id, 'sell');
        $availableQuantity = $totalBuyQuantity - $totalSellQuantity;
        
        if ($availableQuantity < $quantity) {
            throw new \InvalidArgumentException(
                "Quantité insuffisante pour la vente. Vous possédez seulement " . 
                number_format($availableQuantity, 8) . "."
            );
        }
        
        // Créditer le solde utilisateur
        $currentBalance = (float) ($user->euro_balance ?? 0.0);
        $user->euro_balance = $currentBalance + $amount;
        $user->save();
    }
    
    /**
     * Crée un enregistrement de transaction
     */
    private function createTransaction(
        Portfolio $portfolio,
        string $type,
        float $quantity,
        float $price,
        float $amount
    ): Transaction {
        return Transaction::create([
            'portfolio_id' => $portfolio->id,
            'type' => $type,
            'quantity' => $quantity,
            'price_at_transaction' => $price,
            'euro_amount' => $amount,
        ]);
    }
}
