<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedido_linea', function (Blueprint $table) {
            $table->id('pedido_linea_id');
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('variante_id')->nullable();
            $table->string('tipo', 10)->default('talla');
            $table->unsignedBigInteger('tela_id')->nullable();
            $table->string('tela_nombre', 80)->nullable();
            $table->json('medidas')->nullable();
            $table->boolean('requiere_cita')->default(false);
            $table->text('notas')->nullable();
            $table->string('producto_nombre', 255);
            $table->string('talla', 10)->nullable();
            $table->string('color', 40)->nullable();
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            $table->foreign('pedido_id')->references('pedido_id')->on('pedido')->onDelete('cascade');
            $table->foreign('producto_id')->references('producto_id')->on('producto')->onDelete('restrict');
            $table->foreign('variante_id')->references('variante_id')->on('producto_variante')->onDelete('restrict');
            $table->foreign('tela_id')->references('tela_id')->on('tela')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedido_linea');
    }
};
