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
        Schema::create('entidades_externas', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 100)->unique()->comment('Nombre técnico de la tabla externa, p.ej. HPREG05');
            $table->string('descripcion', 255)->nullable()->comment('Descripción legible, p.ej. "Tabla de pacientes"');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entidades_externas');
    }
};
