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
       Schema::create('dc_outs', function (Blueprint $table) {
    $table->id();
    $table->string('dc_number')->unique();
    $table->unsignedBigInteger('project_id');
    $table->date('issue_date');
    $table->string('issued_to')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dc_outs');
    }
};
