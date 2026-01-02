<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        // Get time filter from request (default: 7d)
        $timeFilter = $request->input('time_filter', '7d');
        
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

        // Build series based on time filter
        [$revenueSeries, $tradesSeries] = $this->buildTimeSeries($timeFilter);

        $pendingUsers = User::where('role', 'client')
            ->where('status', 'pending_validation')
            ->limit(20)
            ->orderBy('created_at', 'desc')
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

    /**
     * Build time series data based on time filter
     * 
     * @param string $timeFilter (24h, 7d, 30d, 90d)
     * @return array [revenueSeries, tradesSeries]
     */
    private function buildTimeSeries(string $timeFilter): array
    {
        $revenueSeries = [];
        $tradesSeries = [];

        switch ($timeFilter) {
            case '24h':
                // 24 hours - hourly data points
                $start = Carbon::now()->subHours(23)->startOfHour();
                for ($i = 0; $i < 24; $i++) {
                    $hour = (clone $start)->addHours($i);
                    $revenueSeries[] = (float) Transaction::where('type', 'sell')
                        ->whereHas('portfolio.user', function ($query) {
                            $query->where('role', 'client');
                        })
                        ->whereBetween('created_at', [
                            $hour,
                            (clone $hour)->endOfHour()
                        ])
                        ->sum('euro_amount');
                    $tradesSeries[] = (int) Transaction::whereHas('portfolio.user', function ($query) {
                            $query->where('role', 'client');
                        })
                        ->whereBetween('created_at', [
                            $hour,
                            (clone $hour)->endOfHour()
                        ])
                        ->count();
                }
                break;

            case '7d':
                // 7 days - daily data points
                $start = Carbon::now()->subDays(6)->startOfDay();
                for ($i = 0; $i < 7; $i++) {
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
                break;

            case '30d':
                // 30 days - daily data points
                $start = Carbon::now()->subDays(29)->startOfDay();
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
                break;

            case '90d':
                // 90 days - daily data points (or sample to 30 points for better performance)
                $start = Carbon::now()->subDays(89)->startOfDay();
                // Sample every 3 days to get 30 points for better visualization
                $points = 30;
                $step = 3;
                for ($i = 0; $i < $points; $i++) {
                    $dayIndex = $i * $step;
                    $dayStart = (clone $start)->addDays($dayIndex);
                    $dayEnd = (clone $dayStart)->addDays($step - 1)->endOfDay();
                    if ($dayEnd->isFuture()) {
                        $dayEnd = Carbon::now();
                    }
                    
                    $revenueSeries[] = (float) Transaction::where('type', 'sell')
                        ->whereHas('portfolio.user', function ($query) {
                            $query->where('role', 'client');
                        })
                        ->whereBetween('created_at', [$dayStart, $dayEnd])
                        ->sum('euro_amount');
                    $tradesSeries[] = (int) Transaction::whereHas('portfolio.user', function ($query) {
                            $query->where('role', 'client');
                        })
                        ->whereBetween('created_at', [$dayStart, $dayEnd])
                        ->count();
                }
                break;

            default:
                // Default to 7 days
                $start = Carbon::now()->subDays(6)->startOfDay();
                for ($i = 0; $i < 7; $i++) {
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
        }

        return [$revenueSeries, $tradesSeries];
    }
}
