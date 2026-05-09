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
       Schema::create('project_team_members', function (Blueprint $table) {

    $table->id();

    $table->unsignedBigInteger('project_id');
    $table->string('member_name');
    $table->string('mobile')->nullable();
    $table->string('email')->nullable();
    $table->string('designation')->nullable();
    $table->string('employee_code')->nullable();
    $table->boolean('can_login')->default(0);
    $table->boolean('email_notification')->default(1);
    $table->boolean('sms_notification')->default(0);
    $table->timestamps();

    $table->foreign('project_id')
        ->references('id')
        ->on('projects')
        ->onDelete('cascade');
});
    }


    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_team_members');
    }
};
