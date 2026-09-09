<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('purchases', 'purchase_number')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->string('purchase_number')->unique()->after('id');
            });
        }

        if (! Schema::hasColumn('sales', 'sale_number')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('sale_number')->nullable()->unique()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('purchases', 'purchase_number')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropUnique(['purchase_number']);
                $table->dropColumn('purchase_number');
            });
        }

        if (Schema::hasColumn('sales', 'sale_number')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropUnique(['sale_number']);
                $table->dropColumn('sale_number');
            });
        }
    }
};
