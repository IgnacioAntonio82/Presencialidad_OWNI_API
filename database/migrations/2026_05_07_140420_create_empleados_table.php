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
            Schema::create('empleados', function (Blueprint $table) {

                    $table->id();

                    $table->foreignId('empresa_id')
                        ->constrained('empresas')
                        ->cascadeOnDelete();

                    $table->string('legajo');

                    $table->string('nombre');
                    $table->string('apellido');

                    $table->string('cuil', 20);

                    $table->string('telefono')->nullable();
                    $table->string('email')->nullable();

                    $table->date('fecha_nacimiento')->nullable();

                    // Relación laboral
                    $table->date('fecha_ingreso')->nullable();
                    $table->date('fecha_egreso')->nullable();

                    // Acceso al sistema
                    $table->string('usuario')->nullable();
                    $table->string('password')->nullable();

                    $table->enum('rol', [
                        'empleado',
                        'supervisor',
                        'rrhh',
                        'admin'
                    ])->default('empleado');

                    
                    $table->boolean('activo')->default(true); 

                    $table->timestamps();

                    $table->unique(['empresa_id', 'legajo']);
                    $table->unique(['empresa_id', 'cuil']);
                    $table->unique(['empresa_id', 'usuario']);
                    $table->unique(['empresa_id','email']);
                });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
