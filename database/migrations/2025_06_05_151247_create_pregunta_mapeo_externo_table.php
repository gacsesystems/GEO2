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
        Schema::table('pregunta_mapeo_externo', function (Blueprint $table) {
            // 1) Eliminamos las columnas antiguas, si existían:
            if (Schema::hasColumn('pregunta_mapeo_externo', 'tabla_externa')) {
                $table->dropColumn('tabla_externa');
            }
            if (Schema::hasColumn('pregunta_mapeo_externo', 'columna_externa')) {
                $table->dropColumn('columna_externa');
            }

            // 2) Agregamos las nuevas referencias:
            $table->foreignId('entidad_externa_id')->nullable(false)->constrained('entidades_externas')->onDelete('cascade');
            $table->foreignId('campo_externo_id')->nullable(false)->constrained('campos_externos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pregunta_mapeo_externo', function (Blueprint $table) {
            // Restaurar a la versión antigua si es necesario:
            $table->dropForeign(['entidad_externa_id']);
            $table->dropColumn(['entidad_externa_id']);

            $table->dropForeign(['campo_externo_id']);
            $table->dropColumn(['campo_externo_id']);

            // (Opcional) Si quieres volver a las columnas de texto:
            $table->string('tabla_externa', 100)->after('pregunta_id');
            $table->string('columna_externa', 100)->after('tabla_externa');
        });
    }
};
