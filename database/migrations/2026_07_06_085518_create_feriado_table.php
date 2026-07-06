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
        Schema::create('feriados', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            // Solo para feriados de empresa o sucursal
            $table->foreignId('empresa_id')
                ->nullable()
                ->constrained('empresas')
                ->nullOnDelete();

            $table->foreignId('sucursal_id')
                ->nullable()
                ->constrained('sucursales')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Datos
            |--------------------------------------------------------------------------
            */

            $table->string('nombre', 150);

            $table->date('fecha');

            /*
            |--------------------------------------------------------------------------
            | Ámbito
            |--------------------------------------------------------------------------
            */

            $table->enum('ambito', [

                'nacional',

                'provincial',

                'municipal',

                'empresa',

                'sucursal'

            ])->default('nacional');

            /*
            |--------------------------------------------------------------------------
            | Tipo
            |--------------------------------------------------------------------------
            */

            $table->enum('tipo', [

                'inamovible',

                'trasladable',

                'puente'

            ]);

            /*
            |--------------------------------------------------------------------------
            | Configuración
            |--------------------------------------------------------------------------
            */

            // Si el feriado se repite todos los años
            $table->boolean('recurrente')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Configuración
            |--------------------------------------------------------------------------
            */
           

            // Indica si el feriado fue importado desde un organismo oficial
            $table->boolean('es_oficial')
                ->default(false);


            $table->boolean('activo')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Auditoría
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index([
                'fecha',
                'activo'
            ]);

            $table->index([
                'ambito',
                'activo'
            ]);

            $table->index([
                'empresa_id',
                'activo'
            ]);

            $table->index([
                'sucursal_id',
                'activo'
            ]);

            $table->unique([
                'empresa_id',
                'sucursal_id',
                'fecha',
                'nombre'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feriados');
    }
};