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
    Schema::table('dc_in_items', function (Blueprint $table) {

        // ✅ Make column nullable
        $table->unsignedBigInteger('boq_item_id')->nullable()->change();

        // ❗ Drop old foreign key (important)
        $table->dropForeign(['boq_item_id']);

        // ✅ Add new foreign key with SET NULL
        $table->foreign('boq_item_id')
              ->references('id')
              ->on('boq_items')
              ->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            //
        });
    }
};
