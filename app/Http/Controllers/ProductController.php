<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // Get all products or search by name
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }

        $products = $query->get()->map(function($product){
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,   // updated
                'description' => $product->description,
                'image' => $product->image,
                'image_url' => $product->image ? asset('storage/'.$product->image) : null,
            ];
        });

        return $products;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'stock' => 'required|integer|min:0',   // updated
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($data);

            return response()->json([
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,   // updated
                'description' => $product->description,
                'image' => $product->image,
                'image_url' => $product->image ? asset('storage/'.$product->image) : null,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create product', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Product $product)
    {
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'stock' => $product->stock,   // updated
            'description' => $product->description,
            'image' => $product->image,
            'image_url' => $product->image ? asset('storage/'.$product->image) : null,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'stock' => 'required|integer|min:0',   // updated
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();

            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);

            return response()->json([
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,   // updated
                'description' => $product->description,
                'image' => $product->image,
                'image_url' => $product->image ? asset('storage/'.$product->image) : null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update product', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Product $product)
    {
        try {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();
            return response()->json(['message' => 'Product deleted']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete product', 'error' => $e->getMessage()], 500);
        }
    }

    public function lowStock()
    {
        return Product::where('stock', '<=', 10)->get();  // updated
    }

    public function reorderList()
    {
        return Product::where('stock', '<=', 10)->get();  // updated
    }

    public function deductStock(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($product->stock < $data['quantity']) {   // updated
            return response()->json(['message' => 'Not enough stock'], 400);
        }

        try {
            $product->stock -= $data['quantity'];   // updated
            $product->save();

            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'quantity_changed' => $data['quantity'],   // updated
                'movement_type' => 'OUT',                   // updated
            ]);

            return response()->json(['message' => 'Stock deducted', 'product' => $product]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to deduct stock', 'error' => $e->getMessage()], 500);
        }
    }

    public function addStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $product = Product::findOrFail($request->product_id);
            $product->stock += $request->quantity;   // updated
            $product->save();

            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'quantity_changed' => $request->quantity,   // updated
                'movement_type' => 'IN',                    // updated
            ]);

            return response()->json(['message' => 'Stock added', 'product' => $product]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to add stock', 'error' => $e->getMessage()], 500);
        }
    }
}
