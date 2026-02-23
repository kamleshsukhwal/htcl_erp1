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
            $table->string('Aadhar_Number',12)->unique();
            $table->string('PAN_Number',10)->unique();
            // $table->string('Job_Title');
            $table->enum('Employement_Type',['Full-Time','Part-Time','Contractor','Intern','Apprenticeship']);
            $table->string('Degree_Name');
            $table->string('College_Name');
            $table->integer("Year_of_passing");
            $table->integer('Experience');
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
