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
        Schema::create('qa_checklists', function (Blueprint $table) {
    $table->id();
    //$table->unsignedBigInteger('inspection_id');
    //$table->string('check_point');
    $table->string('expected_value')->nullable();
    $table->string('actual_value')->nullable();
    $table->string('status')->default('pending'); // pass/fail
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qa_checklists');
    }
};
