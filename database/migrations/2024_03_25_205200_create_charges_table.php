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
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->string('FACTURA');
            $table->string('FECHA');
            $table->string('MES');
            $table->string('APLICACION');
            $table->string('RAZON_SOCIAL');
            $table->string('MARCA');
            $table->string('CAMPANIA');
            $table->string('GRUPO');
            $table->text('CONCEPTO');
            $table->string('CANTIDAD_CONCEPTO');
            $table->string('PRECIO_UNITARIO_EN_BS');
            $table->string('BS');
            $table->string('SUS');
            $table->string('T/C');
            $table->string('CUENTAS_CONTABILIDAD');
            $table->string('CUENTA');
            $table->string('CUENTA2');
            $table->string('CODIGO_QUITER');
            $table->string('OBSERVACIONES');
            $table->string('NIT');
            $table->string('OBSERVACIONES2');
            $table->string('USUARIO');
            $table->string('RUTA');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charges');
    }
};
