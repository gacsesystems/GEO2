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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id('id_cliente'); // PK 'id_cliente'
            $table->string('razon_social', 150);
            $table->string('alias', 50)->unique();
            $table->string('ruta_logo', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->integer('limite_encuestas')->default(0);
            $table->dateTime('vigencia')->nullable();
            $table->timestamps();
            $table->foreignId('usuario_registro_id')->nullable()->constrained('usuarios')->onDelete('set null'); // FK a usuarios.id
            $table->foreignId('usuario_modificacion_id')->nullable()->constrained('usuarios')->onDelete('set null'); // FK a usuarios.id
            $table->softDeletes();
            $table->foreignId('usuario_eliminacion_id')->nullable()->constrained('usuarios')->onDelete('set null'); // FK a usuarios.id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
