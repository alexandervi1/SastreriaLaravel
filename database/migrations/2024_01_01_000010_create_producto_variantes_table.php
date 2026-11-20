<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('producto_variante', function (Blueprint $table) {
            $table->id('variante_id');
            $table->unsignedBigInteger('producto_id');
            $table->string('talla', 10);
            $table->string('color', 40)->nullable();
            $table->string('sku', 40)->unique();
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('producto_id')->references('producto_id')->on('producto')->onDelete('cascade');
            $table->unique(['producto_id', 'talla', 'color']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('producto_variante');
    }
};
