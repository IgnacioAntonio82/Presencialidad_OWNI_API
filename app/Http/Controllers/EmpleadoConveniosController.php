<?php

namespace App\Http\Controllers;

use App\Models\Convenios;
use App\Models\Empleado;
use App\Models\EmpleadoConvenio;
use Illuminate\Http\Request;

class EmpleadoConveniosController extends Controller
{
    /**
     * Listar asignaciones de convenios.
     */
    public function index()
    {
        return response()->json(

            EmpleadoConvenio::with([
                'empresa',
                'empleado',
                'convenio'
            ])->get()

        );
    }

    /**
     * Asignar convenio a un empleado.
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

            'convenio_id' => [
                'required',
                'exists:convenios,id'
            ],

            'vigente_desde' => [
                'required',
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

        /*
        |--------------------------------------------------------------------------
        | El empleado pertenece a la empresa.
        |--------------------------------------------------------------------------
        */

        if (

            !Empleado::where(
                'id',
                $datos['empleado_id']
            )
            ->where(
                'empresa_id',
                $datos['empresa_id']
            )
            ->exists()

        ) {

            return response()->json([
                'message' => 'El empleado no pertenece a la empresa.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | El convenio pertenece a la empresa
        | o es un convenio general.
        |--------------------------------------------------------------------------
        */

        if (

            !Convenio::where(
                'id',
                $datos['convenio_id']
            )
            ->where(function ($q) use ($datos) {

                $q->where(
                        'empresa_id',
                        $datos['empresa_id']
                    )
                    ->orWhereNull(
                        'empresa_id'
                    );

            })
            ->exists()

        ) {

            return response()->json([
                'message' => 'El convenio no pertenece a la empresa.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | Vigencia
        |--------------------------------------------------------------------------
        */

        if (

            !empty($datos['vigente_hasta']) &&
            $datos['vigente_hasta'] < $datos['vigente_desde']

        ) {

            return response()->json([
                'message' => 'La fecha de vigencia hasta debe ser posterior a la fecha desde.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | Duplicado
        |--------------------------------------------------------------------------
        */

        $duplicado = EmpleadoConvenio::where(
                'empleado_id',
                $datos['empleado_id']
            )
            ->where(
                'convenio_id',
                $datos['convenio_id']
            )
            ->where(
                'vigente_desde',
                $datos['vigente_desde']
            )
            ->where(
                'vigente_hasta',
                $datos['vigente_hasta']
            )
            ->exists();

        if ($duplicado) {

            return response()->json([
                'message' => 'El convenio ya se encuentra asignado al empleado.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | Superposición de vigencias
        |--------------------------------------------------------------------------
        */

        if (

            $this->tieneSuperposicion(

                $datos['empleado_id'],

                $datos['vigente_desde'],

                $datos['vigente_hasta'] ?? null

            )

        ) {

            return response()->json([
                'message' => 'El empleado ya posee otro convenio vigente para ese período.'
            ], 422);

        }

        $datos['activo'] = $datos['activo'] ?? true;

        $empleadoConvenio = EmpleadoConvenio::create($datos);

        return response()->json(

            $empleadoConvenio->load([
                'empresa',
                'empleado',
                'convenio'
            ]),

            201

        );

    }

    /**
     * Mostrar asignación.
     */
    public function show(
        EmpleadoConvenio $empleadoConvenio
    ) {

        return response()->json(

            $empleadoConvenio->load([
                'empresa',
                'empleado',
                'convenio'
            ])

        );

    }

    /**
     * Actualizar asignación.
     */
    public function update(
        Request $request,
        EmpleadoConvenio $empleadoConvenio
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

            'convenio_id' => [
                'sometimes',
                'exists:convenios,id'
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
            ?? $empleadoConvenio->empresa_id;

        $empleadoId = $datos['empleado_id']
            ?? $empleadoConvenio->empleado_id;

        $convenioId = $datos['convenio_id']
            ?? $empleadoConvenio->convenio_id;

        $vigenteDesde = $datos['vigente_desde']
            ?? $empleadoConvenio->vigente_desde;

        $vigenteHasta = array_key_exists(
                'vigente_hasta',
                $datos
            )
            ? $datos['vigente_hasta']
            : $empleadoConvenio->vigente_hasta;

        /*
        |--------------------------------------------------------------------------
        | El empleado pertenece a la empresa.
        |--------------------------------------------------------------------------
        */

        if (

            !Empleado::where(
                'id',
                $empleadoId
            )
            ->where(
                'empresa_id',
                $empresaId
            )
            ->exists()

        ) {

            return response()->json([
                'message' => 'El empleado no pertenece a la empresa.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | El convenio pertenece a la empresa
        | o es un convenio general.
        |--------------------------------------------------------------------------
        */

        if (

            !Convenio::where(
                'id',
                $convenioId
            )
            ->where(function ($q) use ($empresaId) {

                $q->where(
                        'empresa_id',
                        $empresaId
                    )
                    ->orWhereNull(
                        'empresa_id'
                    );

            })
            ->exists()

        ) {

            return response()->json([
                'message' => 'El convenio no pertenece a la empresa.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | Vigencia
        |--------------------------------------------------------------------------
        */

        if (

            !empty($vigenteHasta) &&
            $vigenteHasta < $vigenteDesde

        ) {

            return response()->json([
                'message' => 'La fecha de vigencia hasta debe ser posterior a la fecha desde.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | Duplicado
        |--------------------------------------------------------------------------
        */

        $duplicado = EmpleadoConvenio::where(
                'empleado_id',
                $empleadoId
            )
            ->where(
                'convenio_id',
                $convenioId
            )
            ->where(
                'vigente_desde',
                $vigenteDesde
            )
            ->where(
                'vigente_hasta',
                $vigenteHasta
            )
            ->where(
                'id',
                '!=',
                $empleadoConvenio->id
            )
            ->exists();

        if ($duplicado) {

            return response()->json([
                'message' => 'El convenio ya se encuentra asignado al empleado.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | Superposición de vigencias
        |--------------------------------------------------------------------------
        */

        if (

            $this->tieneSuperposicion(

                $empleadoId,

                $vigenteDesde,

                $vigenteHasta,

                $empleadoConvenio->id

            )

        ) {

            return response()->json([
                'message' => 'El empleado ya posee otro convenio vigente para ese período.'
            ], 422);

        }

        $empleadoConvenio->update($datos);

        return response()->json(

            $empleadoConvenio->load([
                'empresa',
                'empleado',
                'convenio'
            ])

        );

    }

    /**
     * Eliminar asignación.
     */
    public function destroy(
        EmpleadoConvenio $empleadoConvenio
    ) {

        $empleadoConvenio->delete();

        return response()->json([
            'message' => 'Convenio del empleado eliminado correctamente.'
        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | Métodos privados
    |--------------------------------------------------------------------------
    */

    /**
     * Verifica si un empleado posee otro convenio
     * cuya vigencia se superpone.
     */
    private function tieneSuperposicion(
        int $empleadoId,
        string $vigenteDesde,
        ?string $vigenteHasta,
        ?int $ignorarId = null
    ): bool {

        $vigenteHasta = $vigenteHasta ?? '9999-12-31';

        $query = EmpleadoConvenio::where(
                'empleado_id',
                $empleadoId
            )
            ->where(
                'activo',
                true
            )
            ->where(
                'vigente_desde',
                '<=',
                $vigenteHasta
            )
            ->where(function ($q) use ($vigenteDesde) {

                $q->whereNull(
                        'vigente_hasta'
                    )
                    ->orWhere(
                        'vigente_hasta',
                        '>=',
                        $vigenteDesde
                    );

            });

        if ($ignorarId) {

            $query->where(
                'id',
                '!=',
                $ignorarId
            );

        }

        return $query->exists();

    }

}



