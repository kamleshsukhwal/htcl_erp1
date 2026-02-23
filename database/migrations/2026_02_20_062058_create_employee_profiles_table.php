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
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('aadhar_number',12)->unique();
            $table->string('pan_number',10)->unique();
            // $table->string('Job_Title');
            $table->enum('employement_type',['Full-Time','Part-Time','Contractor','Intern','Apprenticeship']);
            $table->string('degree_name');
            $table->string('college_name');
            $table->integer("year_of_passing");
            $table->integer('experience');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};
