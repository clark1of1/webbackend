<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->renameColumn('type', 'movement_type');
            $table->renameColumn('quantity', 'quantity_changed');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->renameColumn('movement_type', 'type');
            $table->renameColumn('quantity_changed', 'quantity');
        });
    }
};
