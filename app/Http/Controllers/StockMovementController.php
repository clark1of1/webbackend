<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockMovementController extends Controller
{
    public function index()
    {
        return StockMovement::with('product', 'user')->latest()->get();
    }

    public function getMovements($product_id)
    {
        return StockMovement::where('product_id', $product_id)
            ->with('user')
            ->latest()
            ->get();
    }

    public function addStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'quantity_changed' => $request->quantity,   // updated
            'movement_type' => 'IN',                    // updated
        ]);

        $product->stock += $request->quantity;   // updated
        $product->save();

        return response()->json($movement, 201);
    }

    public function reduceStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);

        if ($request->quantity > $product->stock) {   // updated
            return response()->json(['message' => 'Not enough stock'], 400);
        }

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'quantity_changed' => $request->quantity,   // updated
            'movement_type' => 'OUT',                   // updated
        ]);

        $product->stock -= $request->quantity;   // updated
        $product->save();

        return response()->json($movement, 201);
    }
}
