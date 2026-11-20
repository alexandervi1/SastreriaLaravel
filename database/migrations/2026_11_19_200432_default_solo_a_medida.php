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
            $table->boolean('vende_por_talla')->default(false)->change();
            $table->boolean('vende_a_medida')->default(true)->change();
        });

        // La tienda pasa a trabajar solo a medida; las filas heredadas se alinean.
        DB::table('producto')->update([
            'vende_por_talla' => false,
            'vende_a_medida' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->boolean('vende_por_talla')->default(true)->change();
            $table->boolean('vende_a_medida')->default(false)->change();
        });
    }
};
