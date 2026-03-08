<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vendor_attachments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('vendor_id');

            $table->string('file_name'); // stored file name
            $table->string('original_name')->nullable(); // original uploaded name
            $table->string('file_path'); // path like uploads/vendors/
            $table->string('mime_type')->nullable(); // pdf, jpg, png
            $table->integer('file_size')->nullable(); // in bytes

            $table->timestamps();

            $table->foreign('vendor_id')
                  ->references('id')
                  ->on('vendors')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_attachments');
    }
};