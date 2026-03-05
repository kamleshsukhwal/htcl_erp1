<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
<<<<<<< HEAD
        // Skipped duplicate column issue
=======
        Schema::table('clients', function (Blueprint $table) {

          //  $table->string('client_code', 50)->unique()->nullable()->after('id');
       //   $table->string('name')->after('client_code');
         // $table->string('company_name')->nullable();

        //  $table->string('contact_person', 150)->nullable()->after('company_name');
          // $table->string('email', 150)->nullable()->after('contact_person');
          // $table->string('phone', 20)->nullable()->after('email');
            //$table->string('alternate_phone', 20)->nullable();
          //  $table->text('address')->nullable();
        });
>>>>>>> 9fc7058ba548e5b0b43249e0f346817bf1d732ce
    }

    public function down(): void
    {
        //
    }
};