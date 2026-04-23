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
        Schema::table('letter_formats', function (Blueprint $table) {
            // Remove existing foreign key (if already exists), then make column nullable and re-add FK
            $table->dropForeign(['updated_by']);
            $table->unsignedBigInteger('updated_by')->nullable()->change();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letter_formats', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->unsignedBigInteger('updated_by')->nullable(false)->change();
            $table->foreign('updated_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
