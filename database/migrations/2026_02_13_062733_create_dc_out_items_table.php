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
       Schema::create('dc_out_items', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('dc_out_id');
    $table->unsignedBigInteger('boq_item_id');
    $table->decimal('issued_qty', 12, 2);
    $table->timestamps();

    $table->foreign('dc_out_id')->references('id')->on('dc_outs')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dc_out_items');
    }
};
