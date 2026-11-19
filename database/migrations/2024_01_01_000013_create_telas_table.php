<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tela', function (Blueprint $table) {
            $table->id('tela_id');
            $table->string('nombre', 80);
            $table->string('composicion', 120)->nullable();
            $table->string('color', 40);
            $table->decimal('recargo', 10, 2)->default(0);
            $table->string('imagen_url', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tela');
    }
};
