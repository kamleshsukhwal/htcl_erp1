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
       Schema::create('dc_in_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('dc_in_id')->constrained();
    $table->foreignId('boq_id')->constrained();
    $table->decimal('supplied_qty',10,2);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dc_in_items');
    }
};
