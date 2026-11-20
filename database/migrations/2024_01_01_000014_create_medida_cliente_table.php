<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medida_cliente', function (Blueprint $table) {
            $table->id('medida_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('nombre', 60);

            // Saco
            $table->decimal('pecho', 5, 1)->nullable();
            $table->decimal('cintura', 5, 1)->nullable();
            $table->decimal('cadera', 5, 1)->nullable();
            $table->decimal('hombros', 5, 1)->nullable();
            $table->decimal('espalda', 5, 1)->nullable();
            $table->decimal('largo_saco', 5, 1)->nullable();
            $table->decimal('largo_manga', 5, 1)->nullable();
            $table->decimal('cuello', 5, 1)->nullable();

            // Pantalón
            $table->decimal('cintura_pantalon', 5, 1)->nullable();
            $table->decimal('cadera_pantalon', 5, 1)->nullable();
            $table->decimal('tiro', 5, 1)->nullable();
            $table->decimal('muslo', 5, 1)->nullable();
            $table->decimal('entrepierna', 5, 1)->nullable();
            $table->decimal('largo_pantalon', 5, 1)->nullable();
            $table->decimal('bota', 5, 1)->nullable();

            $table->decimal('estatura', 5, 1)->nullable();
            $table->text('notas')->nullable();
            $table->boolean('tomadas_en_taller')->default(false);
            $table->timestamps();

            $table->foreign('usuario_id')->references('usuario_id')->on('usuario')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('medida_cliente');
    }
};
