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
        Schema::create('opciones_pregunta', function (Blueprint $table) {
            $table->id('id_opcion_pregunta');
            $table->foreignId('id_pregunta')->constrained('preguntas', 'id_pregunta')->onDelete('cascade');
            $table->string('texto_opcion', 255);
            $table->string('valor_opcion', 100)->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opciones_pregunta');
    }
};
