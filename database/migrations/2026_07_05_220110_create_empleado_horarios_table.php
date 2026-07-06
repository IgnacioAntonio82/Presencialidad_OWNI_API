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
        Schema::create('empleado_horarios', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->foreignId('empleado_id')
                ->constrained('empleados')
                ->cascadeOnDelete();

            $table->foreignId('horario_id')
                ->constrained('horarios')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Vigencia
            |--------------------------------------------------------------------------
            */

            $table->date('vigente_desde');

            $table->date('vigente_hasta')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Estado
            |--------------------------------------------------------------------------
            */

            $table->boolean('activo')
                ->default(true);

            $table->timestamps();

            $table->softDeletes();


            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'empleado_id',
                'horario_id',
                'vigente_desde'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleado_horarios');
    }



};
