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
        Schema::create('attendance_sessions', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('attendance_day_id');

            $table->datetime('check_in');

            $table->datetime('check_out')->nullable();

            // GPS LOCATION
            $table->string('checkin_latitude')->nullable();

            $table->string('checkin_longitude')->nullable();

            $table->string('checkout_latitude')->nullable();

            $table->string('checkout_longitude')->nullable();

            // CALCULATED HOURS
            $table->decimal(
                'worked_hours',
                5,
                2
            )->default(0);

            $table->text('remarks')->nullable();

            $table->timestamps();

            // FOREIGN KEY
            $table->foreign('attendance_day_id')
                ->references('id')
                ->on('attendance_days')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};