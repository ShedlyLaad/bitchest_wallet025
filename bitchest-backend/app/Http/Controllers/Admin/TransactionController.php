<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionController extends Controller
{
    /**
     * List transactions for admin with optional filters (user_id, symbol, type)
     */
    public function index(Request $request)
    {
        $query = Transaction::query()->with(['portfolio.crypto', 'portfolio.user']);

        if ($request->filled('user_id')) {
            $query->whereHas('portfolio', function ($q) use ($request) {
                $q->where('user_id', $request->input('user_id'));
            });
        }

        if ($request->filled('symbol')) {
            $symbol = $request->input('symbol');
            $query->whereHas('portfolio.crypto', function ($q) use ($symbol) {
                $q->where('symbol', $symbol);
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $perPage = (int) $request->input('per_page', 25);

        $results = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($results);
    }
}
