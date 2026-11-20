<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->string('tipo_prenda', 30)->default('terno_2p')->after('nombre');
            $table->string('publico', 10)->default('caballero')->after('tipo_prenda');
        });

        DB::table('producto')->update([
            'tipo_prenda' => 'terno_2p',
            'publico' => 'caballero',
        ]);
    }

    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->dropColumn(['tipo_prenda', 'publico']);
        });
    }
};
