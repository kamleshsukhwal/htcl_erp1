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
        Schema::create('boq_item_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('boq_item_id')->constrained()->cascadeOnDelete();
    $table->foreignId('boq_id')->constrained()->cascadeOnDelete();

    $table->decimal('old_quantity', 10, 2);
    $table->decimal('new_quantity', 10, 2);

    $table->decimal('old_rate', 10, 2)->nullable();
    $table->decimal('new_rate', 10, 2)->nullable();

    $table->unsignedBigInteger('changed_by');
    $table->string('change_reason')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boq_item_histories');
    }
};
