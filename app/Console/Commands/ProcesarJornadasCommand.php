<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empleado;
use App\Services\JornadaLaboralService;

class ProcesarJornadasCommand extends Command
{
    protected $signature = 'jornadas:procesar';

    protected $description =
        'Procesa jornadas laborales';

    public function handle(
        JornadaLaboralService $service
    ) {
        $fecha = now()
            ->subDay()
            ->toDateString();

        $empleados = Empleado::all();

        foreach ($empleados as $empleado) {

            $service->procesar(
                $empleado->id,
                $fecha
            );
        }

        $this->info(
            'Jornadas procesadas'
        );
    }
}
