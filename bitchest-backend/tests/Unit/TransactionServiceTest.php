<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TransactionService;
use App\Services\PortfolioService;
use App\Services\NotificationService;
use App\Models\User;
use App\Models\CryptoCurrency;
use App\Models\Portfolio;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;

/**
 * Tests unitaires pour TransactionService
 * 
 * Compétences validées :
 * - C3 : Développer des composants d'accès aux données
 * - C8 : Développer des composants dans le langage d'une base de données
 */
class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $transactionService;
    private PortfolioService $portfolioService;
    private NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer des mocks pour les dépendances
        $this->portfolioService = Mockery::mock(PortfolioService::class);
        $this->notificationService = Mockery::mock(NotificationService::class);
        
        $this->transactionService = new TransactionService(
            $this->portfolioService,
            $this->notificationService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test : Traitement d'un achat réussi
     */
    public function test_process_transaction_buy_success(): void
    {
        // Arrange
        $user = User::factory()->create(['euro_balance' => 1000.00]);
        $crypto = CryptoCurrency::factory()->create(['symbol' => 'BTC']);
        $quantity = 0.5;
        $price = 100.00;
        $type = 'buy';

        $this->portfolioService
            ->shouldReceive('updatePortfolio')
            ->once()
            ->andReturnNull();

        $this->notificationService
            ->shouldReceive('checkAndCreatePortfolioNotifications')
            ->once()
            ->with($user)
            ->andReturnNull();

        // Act
        $transaction = $this->transactionService->processTransaction(
            $user,
            $crypto,
            $quantity,
            $price,
            $type
        );

        // Assert
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals('buy', $transaction->type);
        $this->assertEquals($quantity, $transaction->quantity);
        $this->assertEquals($price, $transaction->price_at_transaction);
        $this->assertEquals(50.00, $transaction->euro_amount);

        // Vérifier que le solde a été débité
        $user->refresh();
        $this->assertEquals(950.00, (float) $user->euro_balance);
    }

    /**
     * Test : Échec d'achat avec solde insuffisant
     */
    public function test_process_transaction_buy_insufficient_balance(): void
    {
        // Arrange
        $user = User::factory()->create(['euro_balance' => 10.00]);
        $crypto = CryptoCurrency::factory()->create();
        $quantity = 1.0;
        $price = 100.00;
        $type = 'buy';

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Solde insuffisant');

        $this->transactionService->processTransaction(
            $user,
            $crypto,
            $quantity,
            $price,
            $type
        );
    }

    /**
     * Test : Traitement d'une vente réussi
     */
    public function test_process_transaction_sell_success(): void
    {
        // Arrange
        $user = User::factory()->create(['euro_balance' => 100.00]);
        $crypto = CryptoCurrency::factory()->create(['symbol' => 'BTC']);
        $portfolio = Portfolio::factory()->create([
            'user_id' => $user->id,
            'crypto_currency_id' => $crypto->id,
        ]);

        // Créer des transactions d'achat pour avoir des cryptos à vendre
        Transaction::factory()->count(2)->buy()->create([
            'portfolio_id' => $portfolio->id,
            'quantity' => 1.0,
            'price_at_transaction' => 50.00,
            'euro_amount' => 50.00,
        ]);

        $quantity = 0.5;
        $price = 100.00;
        $type = 'sell';

        $this->portfolioService
            ->shouldReceive('updatePortfolio')
            ->once()
            ->andReturnNull();

        $this->notificationService
            ->shouldReceive('checkAndCreatePortfolioNotifications')
            ->once()
            ->with($user)
            ->andReturnNull();

        // Act
        $transaction = $this->transactionService->processTransaction(
            $user,
            $crypto,
            $quantity,
            $price,
            $type
        );

        // Assert
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals('sell', $transaction->type);

        // Vérifier que le solde a été crédité
        $user->refresh();
        $this->assertEquals(150.00, (float) $user->euro_balance);
    }

    /**
     * Test : Échec de vente avec quantité insuffisante
     */
    public function test_process_transaction_sell_insufficient_quantity(): void
    {
        // Arrange
        $user = User::factory()->create();
        $crypto = CryptoCurrency::factory()->create();
        $portfolio = Portfolio::factory()->create([
            'user_id' => $user->id,
            'crypto_currency_id' => $crypto->id,
        ]);

        // Créer une seule transaction d'achat
        Transaction::factory()->buy()->create([
            'portfolio_id' => $portfolio->id,
            'quantity' => 0.1,
            'price_at_transaction' => 50.00,
        ]);

        $quantity = 1.0; // Plus que ce qui est disponible
        $price = 100.00;
        $type = 'sell';

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantité insuffisante');

        $this->transactionService->processTransaction(
            $user,
            $crypto,
            $quantity,
            $price,
            $type
        );
    }

    /**
     * Test : Validation des paramètres - quantité invalide
     */
    public function test_validate_transaction_params_invalid_quantity(): void
    {
        // Arrange
        $user = User::factory()->create();
        $crypto = CryptoCurrency::factory()->create();
        $quantity = 0.0; // Quantité invalide
        $price = 100.00;
        $type = 'buy';

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La quantité doit être supérieure à');

        $this->transactionService->processTransaction(
            $user,
            $crypto,
            $quantity,
            $price,
            $type
        );
    }

    /**
     * Test : Validation des paramètres - prix invalide
     */
    public function test_validate_transaction_params_invalid_price(): void
    {
        // Arrange
        $user = User::factory()->create();
        $crypto = CryptoCurrency::factory()->create();
        $quantity = 1.0;
        $price = -10.00; // Prix invalide
        $type = 'buy';

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le prix doit être supérieur à 0');

        $this->transactionService->processTransaction(
            $user,
            $crypto,
            $quantity,
            $price,
            $type
        );
    }

    /**
     * Test : Validation des paramètres - type invalide
     */
    public function test_validate_transaction_params_invalid_type(): void
    {
        // Arrange
        $user = User::factory()->create();
        $crypto = CryptoCurrency::factory()->create();
        $quantity = 1.0;
        $price = 100.00;
        $type = 'invalid'; // Type invalide

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le type de transaction doit être 'buy' ou 'sell'");

        $this->transactionService->processTransaction(
            $user,
            $crypto,
            $quantity,
            $price,
            $type
        );
    }

    /**
     * Test : Transaction DB - atomicité
     */
    public function test_transaction_atomicity_on_failure(): void
    {
        // Arrange
        $user = User::factory()->create(['euro_balance' => 1000.00]);
        $crypto = CryptoCurrency::factory()->create();
        $quantity = 1.0;
        $price = 100.00;
        $type = 'buy';

        // Faire échouer le portfolioService pour tester le rollback
        $this->portfolioService
            ->shouldReceive('updatePortfolio')
            ->once()
            ->andThrow(new \Exception('Database error'));

        // Act & Assert
        $this->expectException(\Exception::class);

        try {
            $this->transactionService->processTransaction(
                $user,
                $crypto,
                $quantity,
                $price,
                $type
            );
        } catch (\Exception $e) {
            // Vérifier que le solde n'a pas été modifié (rollback)
            $user->refresh();
            $this->assertEquals(1000.00, (float) $user->euro_balance);

            // Vérifier qu'aucune transaction n'a été créée
            $this->assertEquals(0, Transaction::count());

            throw $e;
        }
    }
}
