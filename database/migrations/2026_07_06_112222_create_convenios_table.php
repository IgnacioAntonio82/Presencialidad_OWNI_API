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
        Schema::create('convenios', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            // Null = Convenio general del sistema.
            // Valor = Convenio propio de una empresa.
            $table->foreignId('empresa_id')
                ->nullable()
                ->constrained('empresas')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Datos
            |--------------------------------------------------------------------------
            */

            $table->string('codigo', 20);

            $table->string('nombre', 150);

            /*
            |--------------------------------------------------------------------------
            | Jornada laboral
            |--------------------------------------------------------------------------
            */

            $table->decimal('jornada_diaria', 4, 2);

            $table->decimal('jornada_semanal', 5, 2);

            /*
            |--------------------------------------------------------------------------
            | Horas extras
            |--------------------------------------------------------------------------
            */

            $table->boolean('permite_horas_extras')
                ->default(true);

            $table->boolean('permite_banco_horas')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Francos compensatorios
            |--------------------------------------------------------------------------
            */

            $table->boolean('permite_compensatorio')
                ->default(false);

            // Si las horas extras generan solo franco
            // en lugar de pago adicional.
            $table->boolean('compensatorio_reemplaza_pago')
                ->default(false);

            $table->boolean('genera_compensatorio_domingo')
                ->default(false);

            $table->boolean('genera_compensatorio_feriado')
                ->default(false);

            $table->boolean('genera_compensatorio_sabado_100')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Horas al 100 %
            |--------------------------------------------------------------------------
            */

            $table->time('sabado_desde_100')
                ->nullable();

            $table->boolean('domingo_100')
                ->default(true);

            $table->boolean('feriado_100')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Nocturnidad
            |--------------------------------------------------------------------------
            */

            $table->boolean('considera_nocturnidad')
                ->default(false);

            $table->time('inicio_nocturnidad')
                ->nullable();

            $table->time('fin_nocturnidad')
                ->nullable();

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

            $table->unique([
                'empresa_id',
                'codigo'
            ]);

            $table->index([
                'empresa_id',
                'activo'
            ]);

            $table->index([
                'vigente_desde',
                'vigente_hasta'
            ]);

            $table->index([
                'codigo'
            ]);

            $table->index([
                'nombre'
            ]);

        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convenios');
    }
};
