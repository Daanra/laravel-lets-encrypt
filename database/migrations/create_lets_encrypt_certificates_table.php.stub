<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLetsEncryptCertificatesTable extends Migration
{
    public function up()
    {
        Schema::create('lets_encrypt_certificates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('domain');
            $table->timestamp('last_renewed_at')->nullable();
            $table->boolean('created')->default(false);
            $table->string('fullchain_path')->nullable();
            $table->string('chain_path')->nullable();
            $table->string('cert_path')->nullable();
            $table->string('privkey_path')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lets_encrypt_certificates');
    }
}
