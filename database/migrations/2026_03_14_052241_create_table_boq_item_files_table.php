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
        Schema::create('boq_item_files', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('boq_item_id');
    $table->string('file_name');
    $table->string('file_path');
    $table->string('file_type')->nullable();
    $table->unsignedBigInteger('uploaded_by')->nullable();
    $table->timestamps();

    $table->foreign('boq_item_id')
        ->references('id')
        ->on('boq_items')
        ->cascadeOnDelete();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_boq_item_files');
    }
};
