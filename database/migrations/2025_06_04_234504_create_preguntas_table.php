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
        Schema::create('preguntas', function (Blueprint $table) {
            $table->id('id_pregunta');
            $table->foreignId('id_seccion')->constrained('secciones_encuesta', 'id_seccion')->onDelete('cascade');
            $table->string('texto_pregunta', 500);
            $table->foreignId('id_tipo_pregunta')->constrained('tipos_pregunta', 'id_tipo_pregunta');
            $table->integer('orden')->default(0);
            $table->boolean('es_obligatoria')->default(false);
            $table->integer('numero_minimo')->nullable();
            $table->integer('numero_maximo')->nullable();
            $table->date('fecha_minima')->nullable();
            $table->date('fecha_maxima')->nullable();
            $table->time('hora_minima')->nullable();
            $table->time('hora_maxima')->nullable();
            $table->string('texto_ayuda', 255)->nullable();
            $table->foreignId('id_pregunta_padre')->nullable()->constrained('preguntas', 'id_pregunta')->onDelete('set null');
            $table->string('valor_condicion_padre', 255)->nullable();
            $table->unsignedBigInteger('id_opcion_condicion_padre')->nullable();
            $table->timestamps();
            $table->foreignId('usuario_registro_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->foreignId('usuario_modificacion_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->softDeletes();
            $table->foreignId('usuario_eliminacion_id')->nullable()->constrained('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preguntas');
    }
};
