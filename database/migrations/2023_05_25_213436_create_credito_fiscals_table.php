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
        Schema::create('CreditoFiscal', function (Blueprint $table) {
            $table->id('id_creditofiscal');
            $table->bigInteger('id_venta')->unsigned();
            $table->bigInteger('id_cliente')->unsigned();

            $table->foreign('id_cliente')
                ->references('id_cliente')
                ->on('Cliente')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('id_venta')
                ->references('id_venta')
                ->on('Venta')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CreditoFiscal');
    }
};
