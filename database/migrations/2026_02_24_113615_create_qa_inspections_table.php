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
        Schema::create('qa_inspections', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('project_id');
    $table->unsignedBigInteger('boq_item_id');
    $table->date('inspection_date');
    $table->string('status')->default('pending'); // pending/approved/rejected/rework
    $table->text('remarks')->nullable();
    $table->unsignedBigInteger('inspected_by');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qa_inspections');
    }
};
