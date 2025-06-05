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
        Schema::create('secciones_encuesta', function (Blueprint $table) {
            $table->id('id_seccion');
            $table->foreignId('id_encuesta')->constrained('encuestas', 'id_encuesta')->onDelete('cascade');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->integer('orden')->default(0);
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
        Schema::dropIfExists('secciones_encuesta');
    }
};
