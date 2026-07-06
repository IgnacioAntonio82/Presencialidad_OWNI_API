<?php

namespace App\Http\Controllers;

use App\Models\Feriado;
use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeriadosController extends Controller
{
    /**
     * Listar feriados.
     */
    public function index()
    {
        return response()->json(

            Feriado::with([
                'empresa',
                'sucursal'
            ])->get()

        );
    }

    /**
     * Crear feriado.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([

            'empresa_id' => [
                'nullable',
                'exists:empresas,id'
            ],

            'sucursal_id' => [
                'nullable',
                'exists:sucursales,id'
            ],

            'nombre' => [
                'required',
                'string',
                'max:150'
            ],

            'fecha' => [
                'required',
                'date'
            ],

            'ambito' => [
                'required',
                Rule::in([
                    'nacional',
                    'provincial',
                    'municipal',
                    'empresa',
                    'sucursal'
                ])
            ],

            'tipo' => [
                'required',
                Rule::in([
                    'inamovible',
                    'trasladable',
                    'puente'
                ])
            ],

            'recurrente' => [
                'sometimes',
                'boolean'
            ],

            'activo' => [
                'sometimes',
                'boolean'
            ]

        ]);

        /*
        |--------------------------------------------------------------------------
        | Validación del ámbito
        |--------------------------------------------------------------------------
        */

        if (
            $datos['ambito'] === 'empresa' &&
            empty($datos['empresa_id'])
        ) {

            return response()->json([
                'message' => 'Debe indicar la empresa.'
            ], 422);

        }

        if (
            $datos['ambito'] === 'sucursal' &&
            (
                empty($datos['empresa_id']) ||
                empty($datos['sucursal_id'])
            )
        ) {

            return response()->json([
                'message' => 'Debe indicar la empresa y la sucursal.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | La sucursal pertenece a la empresa
        |--------------------------------------------------------------------------
        */

        if (!empty($datos['sucursal_id'])) {

            $existe = Sucursal::where(
                    'id',
                    $datos['sucursal_id']
                )
                ->where(
                    'empresa_id',
                    $datos['empresa_id']
                )
                ->exists();

            if (!$existe) {

                return response()->json([
                    'message' => 'La sucursal no pertenece a la empresa.'
                ], 422);

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Evita duplicados
        |--------------------------------------------------------------------------
        */

        $duplicado = Feriado::where(
                'empresa_id',
                $datos['empresa_id'] ?? null
            )
            ->where(
                'sucursal_id',
                $datos['sucursal_id'] ?? null
            )
            ->where(
                'fecha',
                $datos['fecha']
            )
            ->where(
                'nombre',
                $datos['nombre']
            )
            ->exists();

        if ($duplicado) {

            return response()->json([
                'message' => 'El feriado ya existe.'
            ], 422);

        }

        $datos['recurrente'] =
            $datos['recurrente'] ?? false;

        $datos['activo'] =
            $datos['activo'] ?? true;

        $feriado = Feriado::create($datos);

        return response()->json(

            $feriado->load([
                'empresa',
                'sucursal'
            ]),

            201

        );
    }

    /**
     * Mostrar feriado.
     */
    public function show(Feriado $feriado)
    {
        return response()->json(

            $feriado->load([
                'empresa',
                'sucursal'
            ])

        );
    }

    /**
     * Actualizar feriado.
     */
    public function update(
        Request $request,
        Feriado $feriado
    ) {

        $datos = $request->validate([

            'empresa_id' => [
                'sometimes',
                'nullable',
                'exists:empresas,id'
            ],

            'sucursal_id' => [
                'sometimes',
                'nullable',
                'exists:sucursales,id'
            ],

            'nombre' => [
                'sometimes',
                'string',
                'max:150'
            ],

            'fecha' => [
                'sometimes',
                'date'
            ],

            'ambito' => [
                'sometimes',
                Rule::in([
                    'nacional',
                    'provincial',
                    'municipal',
                    'empresa',
                    'sucursal'
                ])
            ],

            'tipo' => [
                'sometimes',
                Rule::in([
                    'inamovible',
                    'trasladable',
                    'puente'
                ])
            ],

            'recurrente' => [
                'sometimes',
                'boolean'
            ],

            'activo' => [
                'sometimes',
                'boolean'
            ]

        ]);

        if ($feriado->es_oficial) {

            return response()->json([
                'message' => 'Los feriados oficiales no pueden modificarse manualmente.'
            ], 422);

        }

        $empresaId = $datos['empresa_id']
            ?? $feriado->empresa_id;

        $sucursalId = $datos['sucursal_id']
            ?? $feriado->sucursal_id;

        $ambito = $datos['ambito']
            ?? $feriado->ambito;

        /*
        |--------------------------------------------------------------------------
        | Validación del ámbito
        |--------------------------------------------------------------------------
        */

        if (
            $ambito === 'empresa'
            && empty($empresaId)
        ) {

            return response()->json([
                'message' => 'Debe indicar la empresa.'
            ], 422);

        }

        if (
            $ambito === 'sucursal'
            && (
                empty($empresaId) ||
                empty($sucursalId)
            )
        ) {

            return response()->json([
                'message' => 'Debe indicar la empresa y la sucursal.'
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | La sucursal pertenece a la empresa
        |--------------------------------------------------------------------------
        */

        if (!empty($sucursalId)) {

            $existe = Sucursal::where('id', $sucursalId)
                ->where('empresa_id', $empresaId)
                ->exists();

            if (!$existe) {

                return response()->json([
                    'message' => 'La sucursal no pertenece a la empresa.'
                ], 422);

            }

        }

        $fecha = $datos['fecha']
            ?? $feriado->fecha;

        $nombre = $datos['nombre']
            ?? $feriado->nombre;

        /*
        |--------------------------------------------------------------------------
        | Evita duplicados
        |--------------------------------------------------------------------------
        */

        $duplicado = Feriado::where(
                'empresa_id',
                $empresaId
            )
            ->where(
                'sucursal_id',
                $sucursalId
            )
            ->where(
                'fecha',
                $fecha
            )
            ->where(
                'nombre',
                $nombre
            )
            ->where(
                'id',
                '!=',
                $feriado->id
            )
            ->exists();

        if ($duplicado) {

            return response()->json([
                'message' => 'El feriado ya existe.'
            ], 422);

        }

        $feriado->update($datos);

        return response()->json(

            $feriado->load([
                'empresa',
                'sucursal'
            ])

        );

    }

    /**
     * Eliminar feriado.
     */
    public function destroy(Feriado $feriado)
    {

        if ($feriado->es_oficial) {

            return response()->json([
                'message' => 'Los feriados oficiales no pueden eliminarse manualmente.'
            ], 422);

        }

        $feriado->delete();

        return response()->json([
            'message' => 'Feriado eliminado correctamente.'
        ]);
    }

    /**
     * Importar feriados desde Argentina Datos.
     */
    public function importarFeriados()
    {
        $anio = now()->year;
        $response = \Illuminate\Support\Facades\Http::get(
            "https://api.argentinadatos.com/v1/feriados/{$anio}"
        );

        if ($response->failed()) {

            return response()->json([
                'message' => 'No fue posible obtener los feriados.'
            ], 500);

        }

        $cantidad = 0;

        foreach ($response->json() as $item) {

            Feriado::updateOrCreate(

                [
                    'empresa_id' => null,
                    'sucursal_id' => null,
                    'fecha' => $item['fecha'],
                    'nombre' => $item['nombre']
                ],

                [
                    'ambito' => 'nacional',

                    'tipo' => strtolower($item['tipo']),

                    'recurrente' => false,

                    'es_oficial' => true,

                    'activo' => true
                ]

            );

            $cantidad++;

        }

        return response()->json([

            'message' => 'Feriados importados correctamente.',

            'cantidad' => $cantidad,

            'anio' => $anio

        ]);

    }
}
