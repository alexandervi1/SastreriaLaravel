<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedido', function (Blueprint $table) {
            $table->id('pedido_id');
            $table->unsignedBigInteger('usuario_id');
            $table->datetime('fecha_pedido')->useCurrent();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('iva', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->date('fecha_entrega_estimada')->nullable();
            $table->string('metodo_entrega', 10)->default('retiro');
            $table->string('direccion_envio', 255)->nullable();

            $table->foreign('usuario_id')->references('usuario_id')->on('usuario')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedido');
    }
};
