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
      
    Schema::create('boq_item_progress', function (Blueprint $table) {
        $table->id();
        $table->foreignId('boq_item_id')->constrained()->cascadeOnDelete();
        $table->decimal('executed_qty', 12, 2);
        $table->date('entry_date');
        $table->text('remarks')->nullable();
        $table->timestamps();
    });
}

  

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boq_item_progress');
    }
};
