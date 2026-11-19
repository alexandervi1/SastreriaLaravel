<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('producto', function (Blueprint $table) {
            $table->id('producto_id');
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->unsignedInteger('stock');
            $table->string('imagen_url', 255)->nullable();
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->timestamps();

            $table->foreign('categoria_id')->references('categoria_id')->on('categoria')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('producto');
    }
};
