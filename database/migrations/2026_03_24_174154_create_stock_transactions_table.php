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
    Schema::create('stock_transactions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('boq_item_id');
        $table->enum('type', ['IN', 'OUT']);
        $table->decimal('quantity', 12, 2);
        $table->string('reference_type')->nullable(); // DC_IN, DC_OUT
        $table->unsignedBigInteger('reference_id')->nullable();
        $table->timestamps();

        $table->foreign('boq_item_id')->references('id')->on('boq_items')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
