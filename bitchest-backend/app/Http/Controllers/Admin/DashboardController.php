<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function summary()
    {
        // Only count client accounts in the dashboard stats
        $totalUsers = User::where('role', 'client')->count();
        $activeUsers = User::where('role', 'client')->where('status', 'active')->count();
        $pendingValidation = User::where('role', 'client')->where('status', 'pending_validation')->count();
        $euroBalance = (float) User::where('role', 'client')->sum('euro_balance');

        // Only count transactions from client users (exclude admin transactions)
        $totalRevenue = (float) Transaction::where('type', 'sell')
            ->whereHas('portfolio.user', function ($query) {
                $query->where('role', 'client');
            })
            ->sum('euro_amount');
        
        $tradesCount = Transaction::whereHas('portfolio.user', function ($query) {
            $query->where('role', 'client');
        })->count();

        // Build 30-day series (exclude admin transactions)
        $start = Carbon::now()->subDays(29)->startOfDay();
        $revenueSeries = [];
        $tradesSeries = [];
        for ($i = 0; $i < 30; $i++) {
            $day = (clone $start)->addDays($i);
            $revenueSeries[] = (float) Transaction::where('type', 'sell')
                ->whereHas('portfolio.user', function ($query) {
                    $query->where('role', 'client');
                })
                ->whereDate('created_at', $day)
                ->sum('euro_amount');
            $tradesSeries[] = (int) Transaction::whereHas('portfolio.user', function ($query) {
                $query->where('role', 'client');
            })
                ->whereDate('created_at', $day)
                ->count();
        }

        $pendingUsers = User::where('role', 'client')
            ->where('status', 'pending_validation')
            ->limit(20)
            ->get(['id', 'name', 'email', 'created_at'])
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'submitDate' => $u->created_at,
                ];
            });

        // Only show recent activities from client users (exclude admin transactions)
        $recentActivities = Transaction::with(['portfolio.crypto', 'portfolio.user'])
            ->whereHas('portfolio.user', function ($query) {
                $query->where('role', 'client');
            })
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'user' => optional(optional($t->portfolio)->user)->name ?? 'Client',
                    'action' => strtoupper($t->type) . ' ' . (optional(optional($t->portfolio)->crypto)->symbol ?? '') . ' : €' . number_format((float) $t->euro_amount, 2),
                    'time' => $t->created_at,
                ];
            });

        return response()->json([
            'totals' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'pending_validation' => $pendingValidation,
                'euro_balance' => $euroBalance,
                'total_revenue' => $totalRevenue,
                'trades_count' => $tradesCount,
            ],
            'revenue_series' => $revenueSeries,
            'trades_series' => $tradesSeries,
            'pending_users' => $pendingUsers,
            'recent_activities' => $recentActivities,
        ]);
    }
}

