<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Services\UserService;
use App\Models\User;
use App\Models\Portfolio;
use App\Models\Transaction;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmailMailable;

class UserController extends Controller
{
    public function store(StoreUserRequest $request, UserService $userService)
    {
        $data = $userService->createClient($request->name, $request->email);

        return response()->json([
            'user' => $data['user'],
            'temporary_password' => $data['password']
        ], 201);
    }

    public function index()
    {
        return response()->json(\App\Models\User::where('role','client')->get());
    }

    public function show($id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        // Get portfolio with PortfolioService
        $portfolioService = app(\App\Services\PortfolioService::class);
        $portfolios = $portfolioService->getUserPortfolio($user);
        
        // Get transaction statistics with relations
        $transactions = Transaction::whereHas('portfolio', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['portfolio.crypto'])->get();
        
        $totalTransactions = $transactions->count();
        $buyTransactions = $transactions->where('type', 'buy')->count();
        $sellTransactions = $transactions->where('type', 'sell')->count();
        $totalVolume = $transactions->sum('total_price');
        
        // Calculate total portfolio value
        $totalPortfolioValue = $portfolios->sum('current_value');
        $totalInvested = $portfolios->sum('invested_value');
        $totalGainLoss = $totalPortfolioValue - $totalInvested;
        
        return response()->json([
            'user' => $user,
            'balance' => (float) ($user->euro_balance ?? 0.0),
            'portfolio' => $portfolios,
            'statistics' => [
                'total_transactions' => $totalTransactions,
                'buy_transactions' => $buyTransactions,
                'sell_transactions' => $sellTransactions,
                'total_volume' => round($totalVolume, 2),
                'total_portfolio_value' => round($totalPortfolioValue, 2),
                'total_invested' => round($totalInvested, 2),
                'total_gain_loss' => round($totalGainLoss, 2),
                'total_gain_loss_percent' => $totalInvested > 0 ? round(($totalGainLoss / $totalInvested) * 100, 2) : 0
            ],
            'recent_transactions' => $transactions->sortByDesc('created_at')->take(10)->values()
        ]);
    }

    public function destroy($id)
    {
        $u = \App\Models\User::findOrFail($id);
        $u->delete();
        return response()->json(['message' => 'Deleted']);
    }
    public function approve($id)
    {
        $user = User::findOrFail($id);
    
        if ($user->isActive()) {
            return response()->json(['message' => 'User already active'], 400);
        }
    
        $user->update([
            'status' => User::STATUS_ACTIVE,
            'must_change_password' => false,
            'euro_balance' => 500.00,
        ]);
    
      
        Mail::to($user->email)->send(new VerifyEmailMailable($user));
    
        return response()->json([
            'message' => 'User approved and account activated',
            'user' => $user
        ]);
    }

    public function block($id)
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            return response()->json(['message' => 'Cannot block an admin'], 400);
        }

        $user->update([
            'status' => User::STATUS_BLOCKED,
            'must_change_password' => false,
        ]);

        return response()->json([
            'message' => 'User blocked',
            'user' => $user,
        ]);
    }
    

}
