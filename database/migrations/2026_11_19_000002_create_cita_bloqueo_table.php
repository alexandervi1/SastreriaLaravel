<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cita_bloqueo', function (Blueprint $table) {
            $table->id('bloqueo_id');
            $table->datetime('inicio');
            $table->datetime('fin');
            $table->string('motivo', 120);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cita_bloqueo');
    }
};
