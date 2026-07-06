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
        Schema::create('novedades', function (Blueprint $table) {

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

            $table->foreignId('sucursal_id')
                ->nullable()
                ->constrained('sucursales')
                ->nullOnDelete();

            // Empleado que autorizó la novedad
            $table->foreignId('empleado_autorizador_id')
                ->nullable()
                ->constrained('empleados')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Tipo de novedad
            |--------------------------------------------------------------------------
            */

            $table->enum('tipo', [

                'vacaciones',

                'enfermedad',

                'accidente',

                'maternidad',

                'paternidad',

                'suspension',

                'capacitacion',

                'comision_servicio',

                'franco_compensatorio',

                'permiso',

                'ausencia_justificada',

                'ausencia_injustificada',

                'llegada_tarde',

                'salida_anticipada',

                'otro'

            ]);

            /*
            |--------------------------------------------------------------------------
            | Período
            |--------------------------------------------------------------------------
            */

            $table->date('fecha_desde');

            $table->date('fecha_hasta');

            /*
            |--------------------------------------------------------------------------
            | Horario (opcional)
            |--------------------------------------------------------------------------
            */

            // Si es NULL aplica a toda la jornada.

            $table->time('hora_desde')
                ->nullable();

            $table->time('hora_hasta')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Información
            |--------------------------------------------------------------------------
            */

            $table->string('motivo', 255)
                ->nullable();

            $table->text('observaciones')
                ->nullable();

            // Observación del supervisor o RRHH
            $table->text('observacion_autorizador')
                ->nullable();

            // Archivo adjunto
            $table->string('archivo')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Estado
            |--------------------------------------------------------------------------
            */

            $table->enum('estado', [

                'pendiente',

                'aprobada',

                'rechazada'

            ])->default('pendiente');

            // Fecha en que fue aprobada/rechazada
            $table->timestamp('fecha_autorizacion')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Configuración
            |--------------------------------------------------------------------------
            */

            $table->boolean('activo')
                ->default(true);

            // Indica si afecta el cálculo de asistencia.
            $table->boolean('afecta_asistencia')
                ->default(true);

            // Prioridad para resolver conflictos entre novedades.
            $table->unsignedTinyInteger('prioridad')
                ->default(1);

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
                'empresa_id',
                'empleado_id'
            ]);

            $table->index([
                'fecha_desde',
                'fecha_hasta'
            ]);

            $table->index([
                'tipo'
            ]);

            $table->index([
                'estado'
            ]);

            $table->index([
                'empleado_id',
                'estado',
                'activo'
            ]);

            /*
            |--------------------------------------------------------------------------
            | Evita duplicados
            |--------------------------------------------------------------------------
            */

            $table->unique([

                'empleado_id',

                'tipo',

                'fecha_desde',

                'fecha_hasta'

            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('novedades');
    }
};
