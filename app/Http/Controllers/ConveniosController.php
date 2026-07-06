<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Convenios;

class ConveniosController extends Controller
{
    /**
     * Listar convenios.
     */
    public function index()
    {
        return response()->json(

            Convenios::with([
                'empresa'
            ])->get()

        );
    }

    /**
     * Crear convenio.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([

            'empresa_id' => [
                'nullable',
                'exists:empresas,id'
            ],

            'codigo' => [
                'required',
                'string',
                'max:20'
            ],

            'nombre' => [
                'required',
                'string',
                'max:150'
            ],

            'jornada_diaria' => [
                'required',
                'numeric',
                'min:1'
            ],

            'jornada_semanal' => [
                'required',
                'numeric',
                'min:1'
            ],

            'permite_horas_extras' => [
                'boolean'
            ],

            'permite_banco_horas' => [
                'boolean'
            ],

            'permite_compensatorio' => [
                'boolean'
            ],

            'compensatorio_reemplaza_pago' => [
                'boolean'
            ],

            'genera_compensatorio_domingo' => [
                'boolean'
            ],

            'genera_compensatorio_feriado' => [
                'boolean'
            ],

            'genera_compensatorio_sabado_100' => [
                'boolean'
            ],

            'sabado_desde_100' => [
                'nullable',
                'date_format:H:i'
            ],

            'domingo_100' => [
                'boolean'
            ],

            'feriado_100' => [
                'boolean'
            ],

            'considera_nocturnidad' => [
                'boolean'
            ],

            'inicio_nocturnidad' => [
                'nullable',
                'date_format:H:i'
            ],

            'fin_nocturnidad' => [
                'nullable',
                'date_format:H:i'
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
        | Jornada
        |--------------------------------------------------------------------------
        */

        if (

            $datos['jornada_diaria'] >
            $datos['jornada_semanal']

        ) {

            return response()->json([
                'message' => 'La jornada diaria no puede ser mayor que la jornada semanal.'
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
        | Nocturnidad
        |--------------------------------------------------------------------------
        */

        if (

            empty($datos['considera_nocturnidad'])

        ) {

            $datos['inicio_nocturnidad'] = null;

            $datos['fin_nocturnidad'] = null;

        } else {

            if (

                empty($datos['inicio_nocturnidad']) ||
                empty($datos['fin_nocturnidad'])

            ) {

                return response()->json([
                    'message' => 'Debe informar el horario de nocturnidad.'
                ], 422);

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Código duplicado
        |--------------------------------------------------------------------------
        */

        if (

            $this->codigoDuplicado(

                $datos['empresa_id'] ?? null,

                $datos['codigo']

            )

        ) {

            return response()->json([
                'message' => 'Ya existe un convenio con ese código.'
            ],422);

        }

        /*
        |--------------------------------------------------------------------------
        | Valores por defecto
        |--------------------------------------------------------------------------
        */

        foreach ([
            'permite_horas_extras',
            'permite_banco_horas',
            'permite_compensatorio',
            'compensatorio_reemplaza_pago',
            'genera_compensatorio_domingo',
            'genera_compensatorio_feriado',
            'genera_compensatorio_sabado_100',
            'domingo_100',
            'feriado_100',
            'considera_nocturnidad',
            'activo'
        ] as $campo) {

            $datos[$campo] = $datos[$campo] ?? false;

        }

        $convenio = Convenios::create($datos);

        return response()->json(

            $convenio->load([
                'empresa'
            ]),

            201

        );
    }

    /**
     * Mostrar convenio.
     */
    public function show(Convenios $convenio)
    {
        return response()->json(

            $convenio->load([
                'empresa'
            ])

        );
    }

    /**
     * Actualizar convenio.
     */
    public function update(
        Request $request,
        Convenios $convenio
    ) {

        $datos = $request->validate([

            'empresa_id' => [
                'sometimes',
                'nullable',
                'exists:empresas,id'
            ],

            'codigo' => [
                'sometimes',
                'string',
                'max:20'
            ],

            'nombre' => [
                'sometimes',
                'string',
                'max:150'
            ],

            'jornada_diaria' => [
                'sometimes',
                'numeric',
                'min:1'
            ],

            'jornada_semanal' => [
                'sometimes',
                'numeric',
                'min:1'
            ],

            'permite_horas_extras' => [
                'boolean'
            ],

            'permite_banco_horas' => [
                'boolean'
            ],

            'permite_compensatorio' => [
                'boolean'
            ],

            'compensatorio_reemplaza_pago' => [
                'boolean'
            ],

            'genera_compensatorio_domingo' => [
                'boolean'
            ],

            'genera_compensatorio_feriado' => [
                'boolean'
            ],

            'genera_compensatorio_sabado_100' => [
                'boolean'
            ],

            'sabado_desde_100' => [
                'nullable',
                'date_format:H:i'
            ],

            'domingo_100' => [
                'boolean'
            ],

            'feriado_100' => [
                'boolean'
            ],

            'considera_nocturnidad' => [
                'boolean'
            ],

            'inicio_nocturnidad' => [
                'nullable',
                'date_format:H:i'
            ],

            'fin_nocturnidad' => [
                'nullable',
                'date_format:H:i'
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
            ?? $convenio->empresa_id;

        $codigo = $datos['codigo']
            ?? $convenio->codigo;

        $jornadaDiaria = $datos['jornada_diaria']
            ?? $convenio->jornada_diaria;

        $jornadaSemanal = $datos['jornada_semanal']
            ?? $convenio->jornada_semanal;

        $vigenteDesde = $datos['vigente_desde']
            ?? $convenio->vigente_desde;

        $vigenteHasta = $datos['vigente_hasta']
            ?? $convenio->vigente_hasta;

        /*
        |--------------------------------------------------------------------------
        | Jornada
        |--------------------------------------------------------------------------
        */

        if ($jornadaDiaria > $jornadaSemanal) {

            return response()->json([
                'message' => 'La jornada diaria no puede ser mayor que la jornada semanal.'
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
        | Nocturnidad
        |--------------------------------------------------------------------------
        */

        $consideraNocturnidad =
            $datos['considera_nocturnidad']
            ?? $convenio->considera_nocturnidad;

        if (!$consideraNocturnidad) {

            $datos['inicio_nocturnidad'] = null;

            $datos['fin_nocturnidad'] = null;

        } else {

            $inicio = $datos['inicio_nocturnidad']
                ?? $convenio->inicio_nocturnidad;

            $fin = $datos['fin_nocturnidad']
                ?? $convenio->fin_nocturnidad;

            if (

                empty($inicio) ||
                empty($fin)

            ) {

                return response()->json([
                    'message' => 'Debe informar el horario de nocturnidad.'
                ], 422);

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Código duplicado
        |--------------------------------------------------------------------------
        */

        if (

            $this->codigoDuplicado(

                $empresaId,

                $codigo,

                $convenio->id

            )

        ) {

            return response()->json([
                'message' => 'Ya existe un convenio con ese código.'
            ],422);

        }

        $convenio->update($datos);

        return response()->json(

            $convenio->load([
                'empresa'
            ])

        );

    }

    /**
     * Eliminar convenio.
     */
    public function destroy(Convenios $convenio)
    {
        /*
        |--------------------------------------------------------------------------
        | Verifica si el convenio está asignado a empleados
        |--------------------------------------------------------------------------
        */

        if ($convenio->empleadoConvenios()->exists()) {

            return response()->json([
                'message' => 'No es posible eliminar el convenio porque posee empleados asociados.'
            ], 422);

        }

        $convenio->delete();

        return response()->json([
            'message' => 'Convenio eliminado correctamente.'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos privados
    |--------------------------------------------------------------------------
    */

    /**
     * Verifica si un convenio está vigente en una fecha.
     */
    private function estaVigente(
        Convenios $convenio,
        string $fecha
    ): bool {

        return
            $convenio->vigente_desde <= $fecha &&
            (
                is_null($convenio->vigente_hasta) ||
                $convenio->vigente_hasta >= $fecha
            );

    }

    /**
     * Verifica si existe otro convenio con el mismo
     * código para la empresa.
     */
    private function codigoDuplicado(
        ?int $empresaId,
        string $codigo,
        ?int $ignorarId = null
    ): bool {

        $query = Convenios::where(
                'empresa_id',
                $empresaId
            )
            ->where(
                'codigo',
                $codigo
            );

        if ($ignorarId) {

            $query->where(
                'id',
                '!=',
                $ignorarId
            );

        }

        return $query->exists();

    }

    /**
     * Verifica que no existan dos convenios
     * con el mismo código y vigencias superpuestas.
     */
    private function tieneSuperposicionVigencia(
        ?int $empresaId,
        string $codigo,
        string $vigenteDesde,
        ?string $vigenteHasta,
        ?int $ignorarId = null
    ): bool {

        $query = Convenios::where(
                'empresa_id',
                $empresaId
            )
            ->where(
                'codigo',
                $codigo
            )
            ->where(
                'vigente_desde',
                '<=',
                $vigenteHasta ?? '9999-12-31'
            )
            ->where(function ($q) use ($vigenteDesde) {

                $q->where(
                        'vigente_hasta',
                        '>=',
                        $vigenteDesde
                    )
                    ->orWhereNull(
                        'vigente_hasta'
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