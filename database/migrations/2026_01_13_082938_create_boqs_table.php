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
       Schema::create('boqs', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('project_id');
    $table->string('boq_name');
    $table->string('discipline');
    $table->enum('status', ['draft','approved'])->default('draft');
    $table->unsignedBigInteger('created_by');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boqs');
    }
};
