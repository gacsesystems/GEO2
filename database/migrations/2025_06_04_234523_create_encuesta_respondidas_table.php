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
        Schema::create('encuestas_respondidas', function (Blueprint $table) {
            $table->id('id_encuesta_respondida');
            $table->foreignId('id_encuesta')->constrained('encuestas', 'id_encuesta')->onDelete('cascade');
            $table->string('correo_respuesta', 255)->nullable();
            $table->foreignId('id_usuario_respuesta')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->dateTime('fecha_inicio_respuesta')->useCurrent();
            $table->dateTime('fecha_fin_respuesta')->nullable();
            $table->json('metadatos')->nullable(); // IP, User-Agent
            $table->timestamps(); // Para saber cuándo se guardó este registro de respuesta
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encuestas_respondidas');
    }
};
