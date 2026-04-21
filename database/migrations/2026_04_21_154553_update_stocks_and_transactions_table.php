<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    // ✅ STOCK TABLE
    Schema::table('stocks', function (Blueprint $table) {
        $table->string('item_name')->nullable()->after('boq_item_id');
    });

    // ✅ STOCK TRANSACTIONS TABLE
    Schema::table('stock_transactions', function (Blueprint $table) {
        $table->string('item_name')->nullable()->after('boq_item_id');
    });
}

public function down()
{
    Schema::table('stocks', function (Blueprint $table) {
        $table->dropColumn('item_name');
    });

    Schema::table('stock_transactions', function (Blueprint $table) {
        $table->dropColumn('item_name');
    });
}
    /**
     * Reverse the migrations.
     */
     
};
