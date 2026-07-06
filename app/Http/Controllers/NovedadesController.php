<?php

namespace App\Http\Controllers;

use App\Models\Novedad;
use App\Models\Empleado;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use Carbon\Carbon;
class NovedadesController extends Controller
{
    /**
     * Listar novedades.
     */
    public function index()
    {
        return response()->json(

            Novedad::with([
                'empresa',
                'empleado',
                'sucursal',
                'autorizador'
            ])->get()

        );
    }

    /**
     * Crear novedad.
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

            'sucursal_id' => [
                'nullable',
                'exists:sucursales,id'
            ],

            'empleado_autorizador_id' => [
                'nullable',
                'exists:empleados,id'
            ],

            'tipo' => [
                'required',
                Rule::in([

                    'vacaciones',

                    'enfermedad',

                    'accidente',

                    'maternidad',

                    'paternidad',

                    'suspension',

                    'capacitacion',

                    'comision',

                    'home_office',

                    'franco_compensatorio',

                    'permiso',

                    'ausencia_justificada',

                    'ausencia_injustificada',

                    'llegada_tarde',

                    'salida_anticipada',

                    'otro'

                ])
            ],

            'fecha_desde' => [
                'required',
                'date'
            ],

            'fecha_hasta' => [
                'required',
                'date',
                'after_or_equal:fecha_desde'
            ],

            'hora_desde' => [
                'nullable',
                'date_format:H:i'
            ],

            'hora_hasta' => [
                'nullable',
                'date_format:H:i',
                'after:hora_desde'
            ],

            'motivo' => [
                'nullable',
                'string',
                'max:255'
            ],

            'observaciones' => [
                'nullable',
                'string'
            ],

            'observacion_autorizador' => [
                'nullable',
                'string'
            ],

            'archivo' => [
                'nullable',
                'string',
                'max:255'
            ],

            'estado' => [
                'sometimes',
                Rule::in([
                    'pendiente',
                    'aprobada',
                    'rechazada'
                ])
            ],

            'fecha_autorizacion' => [
                'nullable',
                'date'
            ],

            'activo' => [
                'sometimes',
                'boolean'
            ],

            'afecta_asistencia' => [
                'sometimes',
                'boolean'
            ],

            'prioridad' => [
                'sometimes',
                'integer',
                'min:1',
                'max:255'
            ]

        ]);

        /*
        |--------------------------------------------------------------------------
        | El empleado pertenece a la empresa
        |--------------------------------------------------------------------------
        */

        if (

            !Empleado::where('id', $datos['empleado_id'])
                ->where('empresa_id', $datos['empresa_id'])
                ->exists()

        ) {

            return response()->json([
                'message' => 'El empleado no pertenece a la empresa.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | La sucursal pertenece a la empresa
        |--------------------------------------------------------------------------
        */

        if (!empty($datos['sucursal_id'])) {

            if (

                !Sucursal::where('id', $datos['sucursal_id'])
                    ->where('empresa_id', $datos['empresa_id'])
                    ->exists()

            ) {

                return response()->json([
                    'message' => 'La sucursal no pertenece a la empresa.'
                ], 422);

            }

        }

        /*
        |--------------------------------------------------------------------------
        | El autorizador pertenece a la empresa
        |--------------------------------------------------------------------------
        */

        if (!empty($datos['empleado_autorizador_id'])) {

            if (

                !Empleado::where(
                    'id',
                    $datos['empleado_autorizador_id']
                )
                ->where(
                    'empresa_id',
                    $datos['empresa_id']
                )
                ->exists()

            ) {

                return response()->json([
                    'message' => 'El autorizador no pertenece a la empresa.'
                ], 422);

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Valores por defecto
        |--------------------------------------------------------------------------
        */

        $datos['estado'] =
            $datos['estado'] ?? 'pendiente';

        $datos['activo'] =
            $datos['activo'] ?? true;

        $datos['afecta_asistencia'] =
            $datos['afecta_asistencia'] ?? true;

        $datos['prioridad'] =
            $datos['prioridad'] ?? 1;

        /*
        |--------------------------------------------------------------------------
        | Evita registros exactamente iguales
        |--------------------------------------------------------------------------
        */

        $duplicado = Novedad::where(
                'empleado_id',
                $datos['empleado_id']
            )
            ->where(
                'tipo',
                $datos['tipo']
            )
            ->where(
                'fecha_desde',
                $datos['fecha_desde']
            )
            ->where(
                'fecha_hasta',
                $datos['fecha_hasta']
            )
            ->exists();

        if ($duplicado) {

            return response()->json([
                'message' => 'La novedad ya existe para el empleado.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | Verifica superposición de novedades
        |--------------------------------------------------------------------------
        */

        if (

            $this->tieneSuperposicion(

                $datos['empleado_id'],

                $datos['fecha_desde'],

                $datos['fecha_hasta'],

                $datos['hora_desde'] ?? null,

                $datos['hora_hasta'] ?? null,

                null,

                $datos['tipo']

            )

        ) {

            return response()->json([

                'message' => 'El empleado posee una novedad superpuesta para ese período.'

            ], 422);

        }



        $novedad = Novedad::create($datos);

        return response()->json(

            $novedad->load([
                'empresa',
                'empleado',
                'sucursal',
                'autorizador'
            ]),

            201

        );
    }

    /**
     * Mostrar novedad.
     */
    public function show(Novedad $novedad)
    {
        return response()->json(

            $novedad->load([
                'empresa',
                'empleado',
                'sucursal',
                'autorizador'
            ])

        );
    }

    /**
     * Actualizar novedad.
     */
    public function update(
        Request $request,
        Novedad $novedad
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

            'sucursal_id' => [
                'nullable',
                'exists:sucursales,id'
            ],

            'empleado_autorizador_id' => [
                'nullable',
                'exists:empleados,id'
            ],

            'tipo' => [
                'sometimes',
                Rule::in([

                    'vacaciones',

                    'enfermedad',

                    'accidente',

                    'maternidad',

                    'paternidad',

                    'suspension',

                    'capacitacion',

                    'comision',

                    'home_office',

                    'franco_compensatorio',

                    'permiso',

                    'ausencia_justificada',

                    'ausencia_injustificada',

                    'llegada_tarde',

                    'salida_anticipada',

                    'otro'

                ])
            ],

            'fecha_desde' => [
                'sometimes',
                'date'
            ],

            'fecha_hasta' => [
                'sometimes',
                'date'
            ],

            'hora_desde' => [
                'nullable',
                'date_format:H:i'
            ],

            'hora_hasta' => [
                'nullable',
                'date_format:H:i'
            ],

            'motivo' => [
                'nullable',
                'string',
                'max:255'
            ],

            'observaciones' => [
                'nullable',
                'string'
            ],

            'observacion_autorizador' => [
                'nullable',
                'string'
            ],

            'archivo' => [
                'nullable',
                'string',
                'max:255'
            ],

            'estado' => [
                'sometimes',
                Rule::in([
                    'pendiente',
                    'aprobada',
                    'rechazada'
                ])
            ],

            'fecha_autorizacion' => [
                'nullable',
                'date'
            ],

            'activo' => [
                'boolean'
            ],

            'afecta_asistencia' => [
                'boolean'
            ],

            'prioridad' => [
                'integer',
                'min:1',
                'max:255'
            ]

        ]);

        $empresaId = $datos['empresa_id']
            ?? $novedad->empresa_id;

        $empleadoId = $datos['empleado_id']
            ?? $novedad->empleado_id;

        $sucursalId = $datos['sucursal_id']
            ?? $novedad->sucursal_id;

        $autorizadorId = $datos['empleado_autorizador_id']
            ?? $novedad->empleado_autorizador_id;

        $fechaDesde = $datos['fecha_desde']
            ?? $novedad->fecha_desde;

        $fechaHasta = $datos['fecha_hasta']
            ?? $novedad->fecha_hasta;

        $horaDesde = $datos['hora_desde']
            ?? $novedad->hora_desde;

        $horaHasta = $datos['hora_hasta']
            ?? $novedad->hora_hasta;

        /*
        |--------------------------------------------------------------------------
        | Validaciones
        |--------------------------------------------------------------------------
        */

        if ($fechaHasta < $fechaDesde) {

            return response()->json([
                'message' => 'La fecha hasta debe ser igual o posterior a la fecha desde.'
            ], 422);

        }

        if (
            !empty($horaDesde) &&
            !empty($horaHasta) &&
            $horaHasta <= $horaDesde
        ) {

            return response()->json([
                'message' => 'La hora hasta debe ser posterior a la hora desde.'
            ], 422);

        }

        if (

            !Empleado::where('id', $empleadoId)
                ->where('empresa_id', $empresaId)
                ->exists()

        ) {

            return response()->json([
                'message' => 'El empleado no pertenece a la empresa.'
            ], 422);

        }

        if (!empty($sucursalId)) {

            if (

                !Sucursal::where('id', $sucursalId)
                    ->where('empresa_id', $empresaId)
                    ->exists()

            ) {

                return response()->json([
                    'message' => 'La sucursal no pertenece a la empresa.'
                ], 422);

            }

        }

        if (!empty($autorizadorId)) {

            if (

                !Empleado::where('id', $autorizadorId)
                    ->where('empresa_id', $empresaId)
                    ->exists()

            ) {

                return response()->json([
                    'message' => 'El autorizador no pertenece a la empresa.'
                ], 422);

            }

        }

        $tipo = $datos['tipo']
            ?? $novedad->tipo;

        $duplicado = Novedad::where(
                'empleado_id',
                $empleadoId
            )
            ->where(
                'tipo',
                $tipo
            )
            ->where(
                'fecha_desde',
                $fechaDesde
            )
            ->where(
                'fecha_hasta',
                $fechaHasta
            )
            ->where(
                'id',
                '!=',
                $novedad->id
            )
            ->exists();

        if ($duplicado) {

            return response()->json([
                'message' => 'Ya existe una novedad idéntica para el empleado.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | Verifica superposición de novedades
        |--------------------------------------------------------------------------
        */

        if (

            $this->tieneSuperposicion(

                $empleadoId,

                $fechaDesde,

                $fechaHasta,

                $horaDesde,

                $horaHasta,

                $novedad->id,

                $tipo

            )

        ) {

            return response()->json([

                'message' => 'El empleado posee una novedad superpuesta para ese período.'

            ], 422);

        }       


        $novedad->update($datos);

        return response()->json(

            $novedad->load([
                'empresa',
                'empleado',
                'sucursal',
                'autorizador'
            ])

        );

    }


    /**
     * Determina si el empleado posee una novedad
     * superpuesta en el período indicado.
     */
    private function tieneSuperposicion(
    int $empleadoId,
    string $fechaDesde,
    string $fechaHasta,
    ?string $horaDesde = null,
    ?string $horaHasta = null,
    ?int $ignorarId = null,
    ?string $tipoNueva = null
    ): bool {

        $query = Novedad::where(
                'empleado_id',
                $empleadoId
            )
            ->where('activo', true)
            ->whereIn('estado', [
                'pendiente',
                'aprobada'
            ])
            ->where(
                'fecha_desde',
                '<=',
                $fechaHasta
            )
            ->where(function ($q) use ($fechaDesde) {

                $q->where(
                        'fecha_hasta',
                        '>=',
                        $fechaDesde
                    )
                    ->orWhereNull('fecha_hasta');

            });

        if ($ignorarId) {

            $query->where(
                'id',
                '!=',
                $ignorarId
            );

        }

        $novedades = $query->get();

        foreach ($novedades as $novedad) {

            /*
            |--------------------------------------------------------------------------
            | Tipos compatibles
            |--------------------------------------------------------------------------
            */

            if (
                $tipoNueva &&
                $this->tiposCompatibles(
                    $tipoNueva,
                    $novedad->tipo
                )
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Jornada completa
            |--------------------------------------------------------------------------
            */

            if (

                is_null($horaDesde) ||
                is_null($horaHasta) ||
                is_null($novedad->hora_desde) ||
                is_null($novedad->hora_hasta)

            ) {

                return true;

            }

            /*
            |--------------------------------------------------------------------------
            | Superposición horaria
            |--------------------------------------------------------------------------
            */

            $inicioNuevo = Carbon::parse($horaDesde);

            $finNuevo = Carbon::parse($horaHasta);

            $inicioExistente = Carbon::parse(
                $novedad->hora_desde
            );

            $finExistente = Carbon::parse(
                $novedad->hora_hasta
            );

            if (

                $inicioNuevo < $finExistente &&
                $finNuevo > $inicioExistente

            ) {

                return true;

            }

        }

        return false;

    }

    private function tiposCompatibles(
    string $nuevo,
    string $existente
    ): bool {

        $compatibles = [

            'capacitacion' => [

                'home_office'

            ],

            'home_office' => [

                'capacitacion'

            ],

        ];

        return in_array(
            $existente,
            $compatibles[$nuevo] ?? []
        );

    }



    /**
     * Eliminar novedad.
     */
    public function destroy(Novedad $novedad)
    {
        $novedad->delete();

        return response()->json([
            'message' => 'Novedad eliminada correctamente.'
        ]);
    }


}
