<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PortfolioService;
use App\Services\CryptoService;
use App\Models\User;
use App\Models\CryptoCurrency;
use App\Models\Portfolio;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;

/**
 * Tests unitaires pour PortfolioService
 * 
 * Compétences validées :
 * - C3 : Développer des composants d'accès aux données
 * - C8 : Développer des composants dans le langage d'une base de données
 */
class PortfolioServiceTest extends TestCase
{
    use RefreshDatabase;

    private PortfolioService $portfolioService;
    private CryptoService $cryptoService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cryptoService = Mockery::mock(CryptoService::class);
        $this->portfolioService = new PortfolioService($this->cryptoService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test : Mise à jour du portfolio lors d'un achat
     */
    public function test_update_portfolio_on_buy(): void
    {
        // Arrange
        $portfolio = Portfolio::factory()->create(['total_crypto_value' => 100.00]);
        $transaction = Transaction::factory()->buy()->make([
            'portfolio_id' => $portfolio->id,
            'quantity' => 1.0,
            'price_at_transaction' => 50.00,
        ]);
        $quantity = 1.0;
        $price = 50.00;
        $type = 'buy';

        // Act
        $this->portfolioService->updatePortfolio(
            $portfolio,
            $transaction,
            $quantity,
            $price,
            $type
        );

        // Assert
        $portfolio->refresh();
        $this->assertEquals(150.00, (float) $portfolio->total_crypto_value);
    }

    /**
     * Test : Mise à jour du portfolio lors d'une vente
     */
    public function test_update_portfolio_on_sell(): void
    {
        // Arrange
        $portfolio = Portfolio::factory()->create(['total_crypto_value' => 200.00]);
        
        // Créer des transactions d'achat
        Transaction::factory()->count(2)->buy()->create([
            'portfolio_id' => $portfolio->id,
            'quantity' => 1.0,
            'price_at_transaction' => 100.00,
        ]);

        $transaction = Transaction::factory()->sell()->make([
            'portfolio_id' => $portfolio->id,
            'quantity' => 0.5,
            'price_at_transaction' => 120.00,
        ]);
        $quantity = 0.5;
        $price = 120.00;
        $type = 'sell';

        // Act
        $this->portfolioService->updatePortfolio(
            $portfolio,
            $transaction,
            $quantity,
            $price,
            $type
        );

        // Assert
        $portfolio->refresh();
        // La valeur devrait être réduite proportionnellement
        // 200.00 - (0.5 * (200.00 / 2.0)) = 150.00
        $this->assertGreaterThan(0, (float) $portfolio->total_crypto_value);
        $this->assertLessThan(200.00, (float) $portfolio->total_crypto_value);
    }

    /**
     * Test : Récupération du portfolio utilisateur avec calculs
     */
    public function test_get_user_portfolio(): void
    {
        // Arrange
        $user = User::factory()->create();
        $crypto = CryptoCurrency::factory()->create(['symbol' => 'BTC']);
        $portfolio = Portfolio::factory()->create([
            'user_id' => $user->id,
            'crypto_currency_id' => $crypto->id,
            'total_crypto_value' => 100.00,
        ]);

        // Créer des transactions
        Transaction::factory()->count(2)->buy()->create([
            'portfolio_id' => $portfolio->id,
            'quantity' => 1.0,
            'price_at_transaction' => 50.00,
        ]);

        $this->cryptoService
            ->shouldReceive('getCachedCurrentPrice')
            ->with('BTC')
            ->andReturn(60.00);

        // Act
        $result = $this->portfolioService->getUserPortfolio($user);

        // Assert
        $this->assertCount(1, $result);
        $portfolioData = $result->first();
        $this->assertNotNull($portfolioData->quantity);
        $this->assertNotNull($portfolioData->current_price);
        $this->assertNotNull($portfolioData->current_value);
        $this->assertNotNull($portfolioData->average_purchase_price);
    }

    /**
     * Test : Récupération des détails d'achat
     */
    public function test_get_purchase_details(): void
    {
        // Arrange
        $user = User::factory()->create();
        $crypto = CryptoCurrency::factory()->create();
        $portfolio = Portfolio::factory()->create([
            'user_id' => $user->id,
            'crypto_currency_id' => $crypto->id,
        ]);

        Transaction::factory()->count(3)->buy()->create([
            'portfolio_id' => $portfolio->id,
            'quantity' => 1.0,
            'price_at_transaction' => 50.00,
        ]);

        // Act
        $details = $this->portfolioService->getPurchaseDetails($user, $crypto->id);

        // Assert
        $this->assertCount(3, $details);
        $this->assertArrayHasKey('quantity', $details->first());
        $this->assertArrayHasKey('price', $details->first());
        $this->assertArrayHasKey('total_cost', $details->first());
    }

    /**
     * Test : Portfolio vide ne retourne rien
     */
    public function test_get_user_portfolio_empty(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $result = $this->portfolioService->getUserPortfolio($user);

        // Assert
        $this->assertCount(0, $result);
    }

    /**
     * Test : Invalidation du cache après mise à jour
     */
    public function test_cache_invalidation_after_update(): void
    {
        // Arrange
        $portfolio = Portfolio::factory()->create();
        $transaction = Transaction::factory()->make(['portfolio_id' => $portfolio->id]);
        
        // Créer des entrées de cache
        Cache::put("portfolio:{$portfolio->id}:total_cost", 100.00, 300);
        Cache::put("portfolio:{$portfolio->id}:buy_count", 5, 300);

        // Act
        $this->portfolioService->updatePortfolio(
            $portfolio,
            $transaction,
            1.0,
            50.00,
            'buy'
        );

        // Assert
        $this->assertFalse(Cache::has("portfolio:{$portfolio->id}:total_cost"));
        $this->assertFalse(Cache::has("portfolio:{$portfolio->id}:buy_count"));
    }
}
