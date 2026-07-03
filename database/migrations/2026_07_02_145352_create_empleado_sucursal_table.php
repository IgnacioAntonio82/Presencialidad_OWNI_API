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
        Schema::create('empleado_sucursal', function (Blueprint $table) {

            $table->id();

            $table->foreignId('empleado_id')
                ->constrained('empleados')
                ->cascadeOnDelete();

            $table->foreignId('sucursal_id')
                ->constrained('sucursales')
                ->cascadeOnDelete();

            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable();            

            $table->enum('modalidad', [
                'presencial',
                'home_office',
                'hibrido'
            ])->default('presencial');

            $table->boolean('validar_gps')->default(true);

            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->unique([
                'empleado_id',
                'sucursal_id',
                'vigente_desde'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleado_sucursal');
    }
};
