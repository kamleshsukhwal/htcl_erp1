<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->text('feedback_text');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('employee_id');
            $table->index('created_at');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
