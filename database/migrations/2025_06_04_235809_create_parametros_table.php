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
        Schema::create('parametros', function (Blueprint $table) {
            $table->id('id_parametro');
            $table->string('clave', 50)->unique();
            $table->string('valor', 255);
            $table->string('descripcion', 255)->nullable();
            $table->timestamps(); // Para saber cuándo se modificó un parámetro
            $table->foreignId('usuario_modificacion_id')->nullable()->constrained('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametros');
    }
};
