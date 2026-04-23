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
        Schema::create('letter_formats', function (Blueprint $table) {
            $table->id();
            $table->string('message_type');
            $table->text('message');
            $table->timestamp('added_time')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->foreignId('added_by')->constrained('users');

            $table->foreignId('updated_by')->constrained('users')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letter_formats');
    }
};
