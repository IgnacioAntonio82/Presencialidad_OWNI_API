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
        Schema::create('horarios', function (Blueprint $table) {

        $table->id();

        /*
        |--------------------------------------------------------------------------
        | Relaciones
        |--------------------------------------------------------------------------
        */

        $table->foreignId('empresa_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('sucursal_id')
            ->constrained('sucursales')
            ->cascadeOnDelete();

        /*
        |--------------------------------------------------------------------------
        | Datos
        |--------------------------------------------------------------------------
        */

        $table->string('codigo', 20)->nullable();

        $table->string('nombre', 100);

        /*
        |--------------------------------------------------------------------------
        | Horario
        |--------------------------------------------------------------------------
        */

        $table->time('hora_ingreso');

        $table->time('hora_salida');

        /*
        |--------------------------------------------------------------------------
        | Días
        |--------------------------------------------------------------------------
        */

        $table->boolean('lunes')->default(false);

        $table->boolean('martes')->default(false);

        $table->boolean('miercoles')->default(false);

        $table->boolean('jueves')->default(false);

        $table->boolean('viernes')->default(false);

        $table->boolean('sabado')->default(false);

        $table->boolean('domingo')->default(false);

        /*
        |--------------------------------------------------------------------------
        | Configuración
        |--------------------------------------------------------------------------
        */

        $table->unsignedSmallInteger('tolerancia_ingreso')
            ->default(0);

        $table->unsignedSmallInteger('tolerancia_salida')
            ->default(0);

        $table->boolean('activo')
            ->default(true);

        $table->timestamps();

        $table->softDeletes();

        /*
        |--------------------------------------------------------------------------
        | Índices
        |--------------------------------------------------------------------------
        */

        $table->index([
            'empresa_id',
            'sucursal_id',
            'activo'
        ]);

        $table->unique([
            'empresa_id',
            'sucursal_id',
            'hora_ingreso',
            'hora_salida',
            'lunes',
            'martes',
            'miercoles',
            'jueves',
            'viernes',
            'sabado',
            'domingo'
        ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
