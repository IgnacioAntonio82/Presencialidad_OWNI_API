<?php

namespace App\Http\Controllers;

use App\Models\EmpleadoHorario;
use App\Models\Empleado;
use App\Models\Horario;
use Illuminate\Http\Request;

class EmpleadoHorariosController extends Controller
{
    /**
     * Listar asignaciones de horarios.
     */
    public function index()
    {
        return response()->json(

            EmpleadoHorario::with([
                'empresa',
                'empleado',
                'horario',
                'horario.sucursal'
            ])->get()

        );
    }

    /**
     * Crear asignación de horario.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([

            'empresa_id' => [
                'required',
                'exists:empresas,id'
            ],

            'empleado_id' => [
                'required',
                'exists:empleados,id'
            ],

            'horario_id' => [
                'required',
                'exists:horarios,id'
            ],

            'vigente_desde' => [
                'required',
                'date'
            ],

            'vigente_hasta' => [
                'nullable',
                'date',
                'after_or_equal:vigente_desde'
            ],

            'activo' => [
                'boolean'
            ]

        ]);

        /*
        |--------------------------------------------------------------------------
        | El empleado pertenece a la empresa
        |--------------------------------------------------------------------------
        */

        if (!Empleado::where('id', $datos['empleado_id'])
            ->where('empresa_id', $datos['empresa_id'])
            ->exists()) {

            return response()->json([
                'message' => 'El empleado no pertenece a la empresa.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | El horario pertenece a la empresa
        |--------------------------------------------------------------------------
        */

        if (!Horario::where('id', $datos['horario_id'])
            ->where('empresa_id', $datos['empresa_id'])
            ->exists()) {

            return response()->json([
                'message' => 'El horario no pertenece a la empresa.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | Verifica superposición de horarios
        |--------------------------------------------------------------------------
        */

        $horario = Horario::findOrFail(
            $datos['horario_id']
        );

        $conflicto = $this->obtenerSuperposicion(

            $datos['empleado_id'],

            $horario,

            $datos['vigente_desde'],

            $datos['vigente_hasta'] ?? null

        );

        if ($conflicto) {

            $asignacion = $conflicto['asignacion'];

            return response()->json([

                'message' => 'El empleado posee un horario superpuesto.',

                'conflicto' => [

                    'dia' => $conflicto['dia'],

                    'horario' => $asignacion->horario->nombre,

                    'sucursal' => $asignacion->horario->sucursal->nombre,

                    'hora_ingreso' => $asignacion->horario->hora_ingreso,

                    'hora_salida' => $asignacion->horario->hora_salida,

                    'vigente_desde' => $asignacion->vigente_desde,

                    'vigente_hasta' => $asignacion->vigente_hasta,

                ]

            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | Evita asignaciones duplicadas
        |--------------------------------------------------------------------------
        */

        $duplicado = EmpleadoHorario::where(
                'empleado_id',
                $datos['empleado_id']
            )
            ->where(
                'horario_id',
                $datos['horario_id']
            )
            ->where(
                'vigente_desde',
                $datos['vigente_desde']
            )
            ->exists();

        if ($duplicado) {

            return response()->json([
                'message' => 'Ese horario ya fue asignado al empleado.'
            ], 422);

        }

        $datos['activo'] = $datos['activo'] ?? true;

        $empleadoHorario = EmpleadoHorario::create($datos);

        return response()->json(

            $empleadoHorario->load([
                'empresa',
                'empleado',
                'horario',
                'horario.sucursal'
            ]),

            201

        );
    }

    /**
     * Mostrar asignación.
     */
    public function show(
        EmpleadoHorario $empleadoHorario
    ) {
        return response()->json(

            $empleadoHorario->load([
                'empresa',
                'empleado',
                'horario',
                'horario.sucursal'
            ])

        );
    }

    /**
     * Actualizar asignación.
     */
    public function update(
        Request $request,
        EmpleadoHorario $empleadoHorario
    ) {

        $datos = $request->validate([

            'empresa_id' => [
                'sometimes',
                'exists:empresas,id'
            ],

            'empleado_id' => [
                'sometimes',
                'exists:empleados,id'
            ],

            'horario_id' => [
                'sometimes',
                'exists:horarios,id'
            ],

            'vigente_desde' => [
                'sometimes',
                'date'
            ],

            'vigente_hasta' => [
                'nullable',
                'date'
            ],

            'activo' => [
                'boolean'
            ]

        ]);

        $empresaId = $datos['empresa_id']
            ?? $empleadoHorario->empresa_id;

        $empleadoId = $datos['empleado_id']
            ?? $empleadoHorario->empleado_id;

        $horarioId = $datos['horario_id']
            ?? $empleadoHorario->horario_id;

        if (!Empleado::where('id', $empleadoId)
            ->where('empresa_id', $empresaId)
            ->exists()) {

            return response()->json([
                'message' => 'El empleado no pertenece a la empresa.'
            ], 422);

        }

        if (!Horario::where('id', $horarioId)
            ->where('empresa_id', $empresaId)
            ->exists()) {

            return response()->json([
                'message' => 'El horario no pertenece a la empresa.'
            ], 422);

        }

        $vigenteDesde = $datos['vigente_desde']
            ?? $empleadoHorario->vigente_desde;

        $vigenteHasta = $datos['vigente_hasta']
            ?? $empleadoHorario->vigente_hasta;

        if (
            $vigenteHasta &&
            $vigenteHasta < $vigenteDesde
        ) {

            return response()->json([
                'message' => 'La fecha hasta debe ser mayor o igual que la fecha desde.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | Verifica superposición de horarios
        |--------------------------------------------------------------------------
        */

        $horarioNuevo = Horario::findOrFail(
            $horarioId
        );

        $conflicto = $this->obtenerSuperposicion(

            $empleadoId,

            $horarioNuevo,

            $vigenteDesde,

            $vigenteHasta,

            $empleadoHorario->id

        );

        if ($conflicto) {

            $asignacion = $conflicto['asignacion'];

            return response()->json([

                'message' => 'El empleado posee un horario superpuesto.',

                'conflicto' => [

                    'dia' => $conflicto['dia'],

                    'horario' => $asignacion->horario->nombre,

                    'sucursal' => $asignacion->horario->sucursal->nombre,

                    'hora_ingreso' => $asignacion->horario->hora_ingreso,

                    'hora_salida' => $asignacion->horario->hora_salida,

                    'vigente_desde' => $asignacion->vigente_desde,

                    'vigente_hasta' => $asignacion->vigente_hasta,

                ]

            ], 422);

        }



        $duplicado = EmpleadoHorario::where(
                'empleado_id',
                $empleadoId
            )
            ->where(
                'horario_id',
                $horarioId
            )
            ->where(
                'vigente_desde',
                $vigenteDesde
            )
            ->where(
                'id',
                '!=',
                $empleadoHorario->id
            )
            ->exists();

        if ($duplicado) {

            return response()->json([
                'message' => 'Ese horario ya fue asignado al empleado.'
            ], 422);

        }

        $empleadoHorario->update($datos);

        return response()->json(

            $empleadoHorario->load([
                'empresa',
                'empleado',
                'horario',
                'horario.sucursal'
            ])

        );
    }

    /**
     * Eliminar asignación.
     */
    public function destroy(
        EmpleadoHorario $empleadoHorario
    ) {

        $empleadoHorario->delete();

        return response()->json([
            'message' => 'Horario eliminado correctamente.'
        ]);

    }


    /**
     * Obtiene el horario que produce una superposición.
     */
    private function obtenerSuperposicion(
        int $empleadoId,
        Horario $horarioNuevo,
        string $vigenteDesde,
        ?string $vigenteHasta,
        ?int $ignorarId = null
    ): ?array {

        $asignaciones = EmpleadoHorario::with([
                'horario',
                'horario.sucursal'
            ])
            ->where('empleado_id', $empleadoId)
            ->where('activo', true)
            ->whereHas('horario', function ($query) {
                $query->where('activo', true);
            })
            ->when(
                $ignorarId,
                fn($q) => $q->where('id', '!=', $ignorarId)
            )
            ->get();

        foreach ($asignaciones as $asignacion) {

            if (!$asignacion->horario) {
                continue;
            }

            $desdeExistente = $asignacion->vigente_desde;
            $hastaExistente = $asignacion->vigente_hasta;

            /*
            |--------------------------------------------------------------------------
            | Superposición de vigencias
            |--------------------------------------------------------------------------
            */

            if (

                $vigenteDesde <= ($hastaExistente ?? '9999-12-31')

                &&

                ($vigenteHasta ?? '9999-12-31') >= $desdeExistente

            ) {

                $horarioExistente = $asignacion->horario;

                /*
                |--------------------------------------------------------------------------
                | Compara días
                |--------------------------------------------------------------------------
                */

                foreach ([
                    'lunes',
                    'martes',
                    'miercoles',
                    'jueves',
                    'viernes',
                    'sabado',
                    'domingo'
                ] as $dia) {

                    if (
                        $horarioNuevo->$dia &&
                        $horarioExistente->$dia
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Compara horas
                        |--------------------------------------------------------------------------
                        */

                        if (

                            $horarioNuevo->hora_ingreso < $horarioExistente->hora_salida

                            &&

                            $horarioNuevo->hora_salida > $horarioExistente->hora_ingreso

                        ) {

                            return [

                                'dia' => ucfirst($dia),

                                'asignacion' => $asignacion

                            ];

                        }

                    }

                }

            }

        }

        return null;

    }



}