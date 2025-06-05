<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->foreign('id_opcion_condicion_padre')
                ->references('id_opcion_pregunta')->on('opciones_pregunta')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            // El nombre de la FK es usualmente: tablaOrigen_columnaOrigen_foreign
            $table->dropForeign('preguntas_id_opcion_condicion_padre_foreign');
            // O de forma más genérica: $table->dropForeign(['id_opcion_condicion_padre']);
        });
    }
};
