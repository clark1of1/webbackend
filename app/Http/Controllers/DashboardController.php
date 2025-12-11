<?php

namespace App\Http\Controllers;

use App\Models\Product;

class DashboardController extends Controller
{
    public function stats()
    {
        return [
            "total_products" => Product::count(),
            "total_stock" => Product::sum("quantity"),
            "total_value" => Product::sum(\DB::raw("price * quantity")),
        ];
    }
}
