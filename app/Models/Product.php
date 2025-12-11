<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 
        'price', 
        'description', 
        'stock',        // renamed from quantity
        'image'
    ];

    // Full image URL accessor
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    // Relationship: a product has many stock movements
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
