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
        Schema::create('sucursales', function (Blueprint $table) {

            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->string('nombre');

            // Tipo Ubicación de sucursal
           $table->enum('tipo_ubicacion', [
                'empresa',      // oficina propia
                'cliente',      // empresa cliente
                'obra',         // obra temporal
                'deposito',     // depósito
                'home_office',  // domicilio del empleado
                'otro'
            ])->default('empresa');

            $table->string('direccion')->nullable();
            $table->string('localidad')->nullable();
            $table->string('provincia')->nullable();
            $table->string('codigo_postal')->nullable();

            $table->string('telefono')->nullable();

            

            /*
            |--------------------------------------------------------------------------
            | Geolocalización
            |--------------------------------------------------------------------------
            */

            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();

            $table->integer('radio_permitido')
                ->default(150);

            /*
            |--------------------------------------------------------------------------
            | Telegram / QR
            |--------------------------------------------------------------------------
            */

            $table->string('codigo_sucursal');

            // Opcional: si preferís guardarlo en la BD
            $table->string('telegram_link')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Estado
            |--------------------------------------------------------------------------
            */

            $table->boolean('activo')
                ->default(true);

            $table->timestamps();

            
            $table->unique([
                'empresa_id',
                'codigo_sucursal'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};
