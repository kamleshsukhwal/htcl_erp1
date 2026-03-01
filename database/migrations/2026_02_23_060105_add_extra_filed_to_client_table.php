<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {

            $table->dropColumn([
                //'client_code',
              // 'name',
              //  'company_name',
              //  'contact_person',
                'email',
                'phone',
                'alternate_phone',
              //  'address'
            ]);
        });
    }
};