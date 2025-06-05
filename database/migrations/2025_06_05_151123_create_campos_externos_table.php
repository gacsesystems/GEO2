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
        Schema::create('campos_externos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entidad_externa_id')->constrained('entidades_externas')->onDelete('cascade');
            $table->string('nombre', 100)->comment('Nombre del campo en la tabla externa, p.ej. Nombre, Edad, Domicilio');
            $table->string('tipo', 50)->default('string')->comment('Tipo de dato esperado: string, integer, date, boolean, etc.');
            $table->string('descripcion', 255)->nullable()->comment('Descripción opcional del campo');
            $table->timestamps();

            $table->unique(['entidad_externa_id', 'nombre'], 'camposentidad_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campos_externos');
    }
};
