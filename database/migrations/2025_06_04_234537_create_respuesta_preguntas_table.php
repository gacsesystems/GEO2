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
        Schema::create('respuestas_pregunta', function (Blueprint $table) {
            $table->id('id_respuesta_pregunta');
            $table->foreignId('id_encuesta_respondida')->constrained('encuestas_respondidas', 'id_encuesta_respondida')->onDelete('cascade');
            $table->foreignId('id_pregunta')->constrained('preguntas', 'id_pregunta')->onDelete('cascade'); // O RESTRICT
            $table->text('valor_texto')->nullable();
            $table->decimal('valor_numerico', 18, 4)->nullable();
            $table->dateTime('valor_fecha')->nullable(); // Puede ser DATE o TIME o DATETIME según el tipo
            $table->boolean('valor_booleano')->nullable();
            $table->foreignId('id_opcion_seleccionada_unica')->nullable()->constrained('opciones_pregunta', 'id_opcion_pregunta')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('respuesta_opcion_seleccionada', function (Blueprint $table) {
            $table->foreignId('id_respuesta_pregunta')->constrained('respuestas_pregunta', 'id_respuesta_pregunta')->onDelete('cascade');
            $table->foreignId('id_opcion_pregunta')->constrained('opciones_pregunta', 'id_opcion_pregunta')->onDelete('cascade');
            $table->primary(['id_respuesta_pregunta', 'id_opcion_pregunta']);
            // No timestamps aquí generalmente
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respuestas_pregunta');
    }
};
