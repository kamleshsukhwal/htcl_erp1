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
        Schema::create('attendance_days', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('project_id');

            // MEMBER ID
            $table->unsignedBigInteger(
                'project_team_member_id'
            );

            $table->date('attendance_date');

            $table->decimal(
                'total_hours',
                5,
                2
            )->default(0);

            $table->enum('status', [
                'present',
                'absent',
                'half_day',
                'leave'
            ])->default('present');

            $table->timestamps();

            // FOREIGN KEYS
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');

            $table->foreign('project_team_member_id')
                ->references('id')
                ->on('project_team_members')
                ->onDelete('cascade');

            // PREVENT DUPLICATE DAILY ENTRY
           $table->unique(
    [
        'project_id',
        'project_team_member_id',
        'attendance_date'
    ],
    'attendance_unique'
);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_days');
    }
};