<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dc_in_items', function (Blueprint $table) {

            // ❌ Drop wrong FK
            $table->dropForeign(['boq_item_id']);

            // ✅ Add correct FK
            $table->foreign('boq_item_id')
                  ->references('id')
                  ->on('boq_items')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('dc_in_items', function (Blueprint $table) {

            // rollback if needed
            $table->dropForeign(['boq_item_id']);

            $table->foreign('boq_item_id')
                  ->references('id')
                  ->on('boqs'); // old wrong mapping
        });
    }
};