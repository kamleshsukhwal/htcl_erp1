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
        Schema::create('boq_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('boq_id')->constrained()->cascadeOnDelete();
    $table->string('sn')->nullable();
    $table->text('description'); // LONG description
    $table->string('unit', 50);
    $table->decimal('quantity', 12, 2);
    $table->decimal('rate', 15, 2);
    $table->decimal('total_amount', 15, 2);
    $table->string('scope')->nullable();
    $table->string('approved_make')->nullable();
    $table->string('offered_make')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boq_items');
    }
};
