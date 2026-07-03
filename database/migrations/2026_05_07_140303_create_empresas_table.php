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
        Schema::create('empresas', function (Blueprint $table) {

            $table->id();

            $table->string('razon_social');
            $table->string('nombre_fantasia')->nullable();

            $table->string('cuit', 20)->unique();
            $table->string('condicion_iva')->nullable();
            $table->string('ingresos_brutos')->nullable();

            $table->string('direccion')->nullable();
            $table->string('localidad')->nullable();
            $table->string('provincia')->nullable();
            $table->string('codigo_postal')->nullable();

            $table->date('fecha_inicio')->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
