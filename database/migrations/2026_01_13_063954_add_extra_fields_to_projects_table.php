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
       Schema::table('projects', function (Blueprint $table) {

    // Responsibility
    $table->unsignedBigInteger('project_manager_id')->nullable()->after('created_by');

    // Team mapping
    $table->json('assigned_users')->nullable()->after('project_manager_id');

    // Business logic
    $table->enum('billing_type', ['boq','fixed','milestone'])
          ->default('boq')->after('status');

    // Progress
    $table->tinyInteger('progress_percent')->default(0)->after('billing_type');

    // Financial
    $table->decimal('approved_budget', 15, 2)->nullable()->after('project_value');
    $table->decimal('actual_cost', 15, 2)->nullable()->after('approved_budget');

    // Notes
    $table->text('remarks')->nullable();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            //
        });
    }
};
