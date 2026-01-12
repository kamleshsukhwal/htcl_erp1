<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->string('project_code')->unique();
            $table->string('project_name');

            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone', 20)->nullable();

            $table->enum('project_type', [
                'Residential',
                'Commercial',
                'Infra'
            ]);

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->decimal('project_value', 15, 2)->nullable();

            $table->enum('status', [
                'draft',
                'active',
                'completed',
                'on-hold'
            ])->default('draft');

            $table->text('description')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();       // created_at, updated_at
            $table->softDeletes();      // deleted_at

            // Optional: Foreign keys (recommended)
            // $table->foreign('created_by')->references('id')->on('users');
            // $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
