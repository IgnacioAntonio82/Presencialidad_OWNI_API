<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;



class HorariosController extends Controller
{
    /**
     * Listar horarios.
     */
    public function index()
    {
        return response()->json(

            Horario::with([
                'empresa',
                'sucursal'
            ])->get()

        );
    }

    /**
     * Crear horario.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([

            'empresa_id' => [
                'required',
                'exists:empresas,id'
            ],

            'sucursal_id' => [
                'required',
                'exists:sucursales,id'
            ],

            'codigo' => [
                'nullable',
                'string',
                'max:20'
            ],

            'nombre' => [
                'required',
                'string',
                'max:100'
            ],

            'hora_ingreso' => [
                'required',
                'date_format:H:i'
            ],

            'hora_salida' => [
                'required',
                'date_format:H:i',
                'after:hora_ingreso'
            ],

            'lunes' => [
                'boolean'
            ],

            'martes' => [
                'boolean'
            ],

            'miercoles' => [
                'boolean'
            ],

            'jueves' => [
                'boolean'
            ],

            'viernes' => [
                'boolean'
            ],

            'sabado' => [
                'boolean'
            ],

            'domingo' => [
                'boolean'
            ],

            'tolerancia_ingreso' => [
                'nullable',
                'integer',
                'min:0'
            ],

            'tolerancia_salida' => [
                'nullable',
                'integer',
                'min:0'
            ],

            'activo' => [
                'boolean'
            ]

        ]);

        foreach ([
            'lunes',
            'martes',
            'miercoles',
            'jueves',
            'viernes',
            'sabado',
            'domingo'
        ] as $dia) {

            $datos[$dia] = $datos[$dia] ?? false;

        }

        if (
            !\App\Models\Sucursal::where('id', $datos['sucursal_id'])
                ->where('empresa_id', $datos['empresa_id'])
                ->exists()
        ) {
            return response()->json([
                'message' => 'La sucursal no pertenece a la empresa.'
            ], 422);
        }



        $dias = [
            'lunes',
            'martes',
            'miercoles',
            'jueves',
            'viernes',
            'sabado',
            'domingo'
        ];

        $tieneDias = collect($dias)
            ->contains(fn ($dia) => !empty($datos[$dia]));

        if (!$tieneDias) {

            return response()->json([
                'message' => 'Debe seleccionar al menos un día de la semana.'
            ], 422);

        }

        $duplicado = Horario::where('empresa_id', $datos['empresa_id'])
            ->where('sucursal_id', $datos['sucursal_id'])
            ->where('hora_ingreso', $datos['hora_ingreso'])
            ->where('hora_salida', $datos['hora_salida'])
            ->where('lunes', $datos['lunes'] ?? false)
            ->where('martes', $datos['martes'] ?? false)
            ->where('miercoles', $datos['miercoles'] ?? false)
            ->where('jueves', $datos['jueves'] ?? false)
            ->where('viernes', $datos['viernes'] ?? false)
            ->where('sabado', $datos['sabado'] ?? false)
            ->where('domingo', $datos['domingo'] ?? false)
            ->exists();

        if ($duplicado) {
            return response()->json([
                'message' => 'Ya existe un horario idéntico para esta sucursal.'
            ], 422);
        }

        



        $horario = Horario::create($datos);

        return response()->json(

            $horario->load([
                'empresa',
                'sucursal'
            ]),

            201

        );
    }

    /**
     * Mostrar horario.
     */
    public function show(Horario $horario)
    {
        return response()->json(

            $horario->load([
                'empresa',
                'sucursal'
            ])

        );
    }

    /**
     * Actualizar horario.
     */
    public function update(
        Request $request,
        Horario $horario
    ) {

        $datos = $request->validate([

            'empresa_id' => [
                'sometimes',
                'exists:empresas,id'
            ],

            'sucursal_id' => [
                'sometimes',
                'exists:sucursales,id'
            ],

            'codigo' => [
                'nullable',
                'string',
                'max:20'
            ],

            'nombre' => [
                'sometimes',
                'string',
                'max:100'
            ],

            'hora_ingreso' => [
                'sometimes',
                'date_format:H:i'
            ],

            'hora_salida' => [
                'sometimes',
                'date_format:H:i'
            ],

            'lunes' => [
                'boolean'
            ],

            'martes' => [
                'boolean'
            ],

            'miercoles' => [
                'boolean'
            ],

            'jueves' => [
                'boolean'
            ],

            'viernes' => [
                'boolean'
            ],

            'sabado' => [
                'boolean'
            ],

            'domingo' => [
                'boolean'
            ],

            'tolerancia_ingreso' => [
                'integer',
                'min:0'
            ],

            'tolerancia_salida' => [
                'integer',
                'min:0'
            ],

            'activo' => [
                'boolean'
            ]

        ]);

         foreach ([
            'lunes',
            'martes',
            'miercoles',
            'jueves',
            'viernes',
            'sabado',
            'domingo'
        ] as $dia) {

            $datos[$dia] = $datos[$dia] ?? $horario->$dia;

        }


        $empresaId = $datos['empresa_id'] ?? $horario->empresa_id;
        $sucursalId = $datos['sucursal_id'] ?? $horario->sucursal_id;

        $horaIngreso = $datos['hora_ingreso']
            ?? $horario->hora_ingreso;

        $horaSalida = $datos['hora_salida']
            ?? $horario->hora_salida;

        if (
            !\App\Models\Sucursal::where('id', $sucursalId)
                ->where('empresa_id', $empresaId)
                ->exists()
        ) {
            return response()->json([
                'message' => 'La sucursal no pertenece a la empresa.'
            ], 422);
        }

        $duplicado = Horario::where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->where('hora_ingreso', $horaIngreso)
            ->where('hora_salida', $horaSalida)
            ->where('lunes', $datos['lunes'] ?? $horario->lunes)
            ->where('martes', $datos['martes'] ?? $horario->martes)
            ->where('miercoles', $datos['miercoles'] ?? $horario->miercoles)
            ->where('jueves', $datos['jueves'] ?? $horario->jueves)
            ->where('viernes', $datos['viernes'] ?? $horario->viernes)
            ->where('sabado', $datos['sabado'] ?? $horario->sabado)
            ->where('domingo', $datos['domingo'] ?? $horario->domingo)
            ->where('id', '!=', $horario->id)
            ->exists();

        if ($duplicado) {
            return response()->json([
                'message' => 'Ya existe un horario idéntico para esta sucursal.'
            ], 422);
        }

        $dias = [
            'lunes',
            'martes',
            'miercoles',
            'jueves',
            'viernes',
            'sabado',
            'domingo'
        ];

        $tieneDias = collect($dias)
            ->contains(fn ($dia) => $datos[$dia] ?? $horario->$dia);

        if (!$tieneDias) {
            return response()->json([
                'message' => 'Debe seleccionar al menos un día de la semana.'
            ], 422);
        }



        

        if ($horaSalida <= $horaIngreso) {

            return response()->json([
                'message' => 'La hora de salida debe ser posterior a la hora de ingreso.'
            ], 422);

        }

       




        $horario->update($datos);

        return response()->json(

            $horario->load([
                'empresa',
                'sucursal'
            ])

        );
    }

    /**
     * Eliminar horario.
     */
    public function destroy(Horario $horario)
    {
        $horario->delete();

        return response()->json([
            'message' => 'Horario eliminado correctamente.'
        ]);
    }
}