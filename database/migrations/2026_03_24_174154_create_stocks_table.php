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
    Schema::create('stocks', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('boq_item_id');
        $table->decimal('available_qty', 12, 2)->default(0);
        $table->timestamps();

        $table->foreign('boq_item_id')->references('id')->on('boq_items')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
