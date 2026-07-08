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
        Schema::create('marcaciones', function (Blueprint $table) {

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

            // Dispositivo desde donde se realizó la marcación
            $table->foreignId('empleado_dispositivo_id')
                ->nullable()
                ->constrained('empleado_dispositivos')
                ->nullOnDelete();

            // Empleado que autorizó/modificó la marcación
            $table->foreignId('empleado_autorizador_id')
                ->nullable()
                ->constrained('empleados')
                ->nullOnDelete();

            $table->foreignId('sucursal_id')
                ->nullable()
                ->constrained('sucursales')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Tipo de marcación
            |--------------------------------------------------------------------------
            */

            $table->enum('tipo', [

                'ingreso',

                'salida',

                'almuerzo_inicio',

                'almuerzo_fin',

                'pausa_inicio',

                'pausa_fin'

            ]);

            /*
            |--------------------------------------------------------------------------
            | Fecha y hora
            |--------------------------------------------------------------------------
            */

            $table->date('fecha');

            $table->timestamp('fecha_hora');

            /*
            |--------------------------------------------------------------------------
            | GPS
            |--------------------------------------------------------------------------
            */

            $table->decimal('latitud', 10, 7)
                ->nullable();

            $table->decimal('longitud', 10, 7)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Origen de la marcación
            |--------------------------------------------------------------------------
            */

            $table->enum('origen', [

                'telegram',

                'whatsapp',

                'web',

                'app',

                'api'

            ])->default('telegram');

            /*
            |--------------------------------------------------------------------------
            | Marcación manual
            |--------------------------------------------------------------------------
            */

            $table->boolean('es_manual')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Estado
            |--------------------------------------------------------------------------
            */

            $table->enum('estado', [

                'pendiente',

                'confirmada',

                'rechazada'

            ])->default('pendiente');

            /*
            |--------------------------------------------------------------------------
            | Dispositivo físico
            |--------------------------------------------------------------------------
            */

            $table->string('id_dispositivo', 100)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Observaciones
            |--------------------------------------------------------------------------
            */

            $table->string('motivo', 255)
                ->nullable();

            $table->text('notas')
                ->nullable();

            $table->softDeletes();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index([
                'empresa_id',
                'empleado_id',
                'fecha'
            ]);

            $table->index('fecha_hora');

            $table->index('tipo');

            $table->index('estado');

            $table->index('origen');

            /*
            |--------------------------------------------------------------------------
            | Evita marcaciones duplicadas
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'empleado_id',
                'fecha_hora',
                'tipo'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marcaciones');
    }
};
