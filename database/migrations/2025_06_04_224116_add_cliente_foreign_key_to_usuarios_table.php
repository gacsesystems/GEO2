<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreign('id_cliente')
                ->references('id_cliente')->on('clientes') // Referencia a clientes.id_cliente
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // El nombre de la FK es usualmente: tablaOrigen_columnaOrigen_foreign
            $table->dropForeign('usuarios_id_cliente_foreign');
        });
    }
};
