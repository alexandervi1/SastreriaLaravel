<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cita', function (Blueprint $table) {
            $table->string('lugar', 10)->default('taller')->after('tipo');
            $table->string('direccion_cliente', 255)->nullable()->after('lugar');
        });

        DB::table('cita')->update(['lugar' => 'taller']);
    }

    public function down(): void
    {
        Schema::table('cita', function (Blueprint $table) {
            $table->dropColumn(['lugar', 'direccion_cliente']);
        });
    }
};
