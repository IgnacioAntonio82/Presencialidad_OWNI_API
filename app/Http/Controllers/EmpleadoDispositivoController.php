<?php

namespace App\Http\Controllers;

use App\Models\EmpleadoDispositivo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmpleadoDispositivoController extends Controller
{
    /**
     * Listar dispositivos.
     */
    public function index()
    {
        return response()->json(
            EmpleadoDispositivo::with([
                'empresa',
                'empleado'
            ])->get()
        );
    }

    /**
     * Crear dispositivo.
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

            'tipo' => [
                'required',
                Rule::in([
                    'telegram',
                    'whatsapp',
                    'app'
                ])
            ],

            'identificador' => [
                'required',
                'string',
                'max:255',
                'unique:empleado_dispositivos,identificador'
            ],

            'usuario' => [
                'nullable',
                'string',
                'max:255'
            ],

            'celular' => [
                'nullable',
                'string',
                'max:20'
            ],

            'activo' => [
                'boolean'
            ]

        ]);

        $dispositivo = EmpleadoDispositivo::create($datos);

        return response()->json(
            $dispositivo->load([
                'empresa',
                'empleado'
            ]),
            201
        );
    }

    /**
     * Mostrar dispositivo.
     */
    public function show(EmpleadoDispositivo $empleadoDispositivo)
    {
        return response()->json(

            $empleadoDispositivo->load([
                'empresa',
                'empleado'
            ])

        );
    }

    /**
     * Actualizar dispositivo.
     */
    public function update(
        Request $request,
        EmpleadoDispositivo $empleadoDispositivo
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

            'tipo' => [
                'sometimes',
                Rule::in([
                    'telegram',
                    'whatsapp',
                    'app'
                ])
            ],

            'identificador' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique(
                    'empleado_dispositivos',
                    'identificador'
                )->ignore($empleadoDispositivo->id)
            ],

            'usuario' => [
                'nullable',
                'string',
                'max:255'
            ],

            'celular' => [
                'nullable',
                'string',
                'max:20'
            ],

            'activo' => [
                'boolean'
            ]

        ]);

        $empleadoDispositivo->update($datos);

        return response()->json(

            $empleadoDispositivo->load([
                'empresa',
                'empleado'
            ])

        );
    }

    /**
     * Eliminar dispositivo.
     */
    public function destroy(
        EmpleadoDispositivo $empleadoDispositivo
    ) {

        $empleadoDispositivo->delete();

        return response()->json([
            'message' => 'Dispositivo eliminado correctamente.'
        ]);

    }
}
