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
        Schema::create('Cliente', function (Blueprint $table) {
            $table->id('id_cliente');
            $table->string('nombre_cliente',50);
            $table->string('apellido_cliente',50);
            $table->string('departamento_cliente',50);
            $table->string('direccion_cliente',50);
            $table->string('dui_cliente',10)->nullable();;
            $table->string('nit_cliente',20)->nullable();;
            $table->string('nrc_cliente',20);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Cliente');
    }
};
