<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qa_inspection_items', function (Blueprint $table) {
            $table->id();

            // 🔗 Belongs to Inspection
            $table->foreignId('inspection_id')
                  ->constrained('qa_inspections')
                  ->cascadeOnDelete();

            // 🔗 Belongs to Checklist Item (Template Question)
            $table->foreignId('checklist_item_id')
                  ->constrained('qa_checklist_items')
                  ->cascadeOnDelete();

            // 📝 Result / Answer
            $table->string('result');

            // Optional remarks
            $table->text('remarks')->nullable();

            $table->timestamps();

            // Prevent duplicate answers for same question
            $table->unique(['inspection_id', 'checklist_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_inspection_items');
    }
};