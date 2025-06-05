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
        Schema::create('encuestas', function (Blueprint $table) {
            $table->id('id_encuesta');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->foreignId('id_cliente')->constrained('clientes', 'id_cliente')->onDelete('cascade');
            $table->boolean('es_cuestionario')->default(false)->comment('1 = es cuestionario de paciente, 0 = encuesta genérica');
            $table->date('fecha_inicio')->nullable()->comment('Solo válido si es cuestionario; fecha inicial de vigencia');
            $table->date('fecha_fin')->nullable()->comment('Solo válido si es cuestionario; fecha final de vigencia');
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
        Schema::dropIfExists('encuestas');
    }
};
