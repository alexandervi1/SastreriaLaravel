<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('producto_imagen', function (Blueprint $table) {
            $table->id('imagen_id');
            $table->unsignedBigInteger('producto_id');
            $table->string('url', 255);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->string('alt', 160);
            $table->timestamps();

            $table->foreign('producto_id')->references('producto_id')->on('producto')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('producto_imagen');
    }
};
