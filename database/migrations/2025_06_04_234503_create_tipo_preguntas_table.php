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
        Schema::create('tipos_pregunta', function (Blueprint $table) {
            $table->id('id_tipo_pregunta');
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->boolean('requiere_opciones')->default(false);
            $table->boolean('permite_min_max_numerico')->default(false);
            $table->boolean('permite_min_max_fecha')->default(false);
            $table->boolean('es_seleccion_multiple')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_pregunta');
    }
};
