<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('carrito', function (Blueprint $table) {
            $table->id('carrito_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('variante_id')->nullable();
            $table->integer('cantidad')->default(1);
            $table->timestamp('fecha_agregado')->useCurrent();

            $table->index(['usuario_id', 'producto_id']);
            $table->foreign('usuario_id')->references('usuario_id')->on('usuario')->onDelete('cascade');
            $table->foreign('producto_id')->references('producto_id')->on('producto')->onDelete('cascade');
            $table->foreign('variante_id')->references('variante_id')->on('producto_variante')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('carrito');
    }
};
