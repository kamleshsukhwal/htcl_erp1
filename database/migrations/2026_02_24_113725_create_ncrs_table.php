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
        Schema::create('ncrs', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('project_id');
    $table->unsignedBigInteger('boq_item_id');
    $table->text('issue_description');
    $table->unsignedBigInteger('reported_by');
    $table->unsignedBigInteger('assigned_to')->nullable();
    $table->string('status')->default('open'); // open/closed
    $table->text('corrective_action')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ncrs');
    }
};
