<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qa_checklist_items', function (Blueprint $table) {
            $table->id();

            // 🔗 Relationship to Checklist
            $table->foreignId('checklist_id')
                  ->constrained('qa_checklists')
                  ->cascadeOnDelete();

            // 📝 The actual checkpoint/question
            $table->string('check_point');

            // 📌 Type of answer expected
            $table->enum('type', ['pass_fail', 'number', 'text'])
                  ->default('pass_fail');

            // Optional: make item mandatory or not
            $table->boolean('is_required')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_checklist_items');
    }
};