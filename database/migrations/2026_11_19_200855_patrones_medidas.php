<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medida_cliente', function (Blueprint $table) {
            $table->string('patron', 10)->default('caballero')->after('nombre');
            $table->decimal('busto', 5, 1)->nullable()->after('pecho');
            $table->decimal('talle_delantero', 5, 1)->nullable()->after('espalda');
            $table->decimal('talle_espalda', 5, 1)->nullable()->after('talle_delantero');
            $table->decimal('largo_falda', 5, 1)->nullable()->after('largo_pantalon');
        });

        DB::table('medida_cliente')->update(['patron' => 'caballero']);
    }

    public function down(): void
    {
        Schema::table('medida_cliente', function (Blueprint $table) {
            $table->dropColumn(['patron', 'busto', 'talle_delantero', 'talle_espalda', 'largo_falda']);
        });
    }
};
