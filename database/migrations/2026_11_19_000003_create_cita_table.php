<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cita', function (Blueprint $table) {
            $table->id('cita_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('tipo', 20);
            $table->datetime('inicio');
            $table->datetime('fin');
            $table->string('estado', 20)->default('Solicitada');
            $table->unsignedBigInteger('pedido_id')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('inicio');
            $table->foreign('usuario_id')->references('usuario_id')->on('usuario')->onDelete('restrict');
            $table->foreign('pedido_id')->references('pedido_id')->on('pedido')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cita');
    }
};
