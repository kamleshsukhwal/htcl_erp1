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

    /**** run migration if error delete boq_item_id forginkkey in op item table thans uncomment below 2  and run migration */
Schema::table('purchase_order_items', function (Blueprint $table) {
    $table->dropForeign(['boq_item_id']);
});

/*
Schema::table('purchase_order_items', function (Blueprint $table) {
    $table->unsignedBigInteger('boq_item_id')->nullable()->change();
});

 Schema::table('purchase_order_items', function (Blueprint $table) {
    $table->foreign('boq_item_id')
          ->references('id')
          ->on('boq_items')
          ->onDelete('set null'); // 🔥 KEY LINE
});

*/
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
