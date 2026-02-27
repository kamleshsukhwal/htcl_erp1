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
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unique(['employee_id', 'document_type']);
            $table->string('document_name');
            $table->string('document_path');
            $table->enum('document_type',['resume','pancard','aadhar_card','address_proof',
            'offer_letter','employment_contract','degree_certificate','bank_detail','tax_document','preformance_review','experience_letter']); // e.g., passport, ID card, etc.
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
