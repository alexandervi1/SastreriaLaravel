<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_linea', function (Blueprint $table) {
            $table->decimal('precio_hechura', 10, 2)->nullable()->after('precio_unitario');
            $table->decimal('precio_tela', 10, 2)->nullable()->after('precio_hechura');
        });
    }

    public function down(): void
    {
        Schema::table('pedido_linea', function (Blueprint $table) {
            $table->dropColumn(['precio_hechura', 'precio_tela']);
        });
    }
};
