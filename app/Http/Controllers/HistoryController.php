<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    // Admin only: see all users' history
    public function all()
    {
        return StockMovement::with('user', 'product')->latest()->get();
    }

    // User only: see their own history
    public function myHistory()
    {
        $userId = Auth::id();
        return StockMovement::with('product')
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }
}
